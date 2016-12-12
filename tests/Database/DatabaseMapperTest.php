<?php

namespace Solution10\Atlas\Tests\Database;

use Doctrine\Common\Cache\ArrayCache;
use Solution10\Atlas\Database\DatabaseMapper;
use Solution10\Atlas\Database\Logger;
use Solution10\Atlas\HasMapper;
use Solution10\Atlas\HasTimestamps;
use Solution10\Atlas\MapperInterface;
use Solution10\Atlas\PHPUnit\BasicDatabase;
use Solution10\Atlas\PHPUnit\TestCase;
use Solution10\Atlas\Tests\Stubs\User;
use Solution10\Atlas\Tests\Stubs\UserCRUD;

class DatabaseMapperTest extends TestCase
{
    use BasicDatabase;

    /**
     * @return  DatabaseMapper
     */
    protected function getMapper()
    {
        return new class extends DatabaseMapper
        {
            protected $modelInstance;

            public function getTableName(): string
            {
                return 'users';
            }

            public function getConnectionName(): string
            {
                return 'default';
            }

            public function setModelInstance($model)
            {
                $this->modelInstance = $model;
                return $this;
            }

            public function getModelInstance()
            {
                return (isset($this->modelInstance))? $this->modelInstance : new User();
            }

            protected function getCreateData($model): array
            {
                return [
                    'name' => $model->getName()
                ];
            }

            protected function getUpdateData($model): array
            {
                return [
                    'name' => $model->getName()
                ];
            }
        };
    }

    public function testCreate()
    {
        $u = new User();
        $u->setName('Alex');

        $mapper = $this->getMapper();
        $this->assertEquals($u, $mapper->create($u));
        $this->assertEquals(1, $u->getId());

        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alex'], $this->conn->fetch('SELECT * FROM users'));
    }

    public function testCreateWithTimestamps()
    {
        $u = new class extends User implements HasTimestamps
        {
            use \Solution10\Atlas\Parts\HasTimestamps;
        };
        $u->setName('Alex');

        $mapper = $this->getMapper();
        $this->assertEquals($u, $mapper->create($u));

        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alex'], $this->conn->fetch('SELECT * FROM users'));

        $this->assertInstanceOf(\DateTime::class, $u->getCreated());
        $this->assertNull($u->getUpdated());
    }

    public function testBasicQueries()
    {
        $mapper = $this->getMapper();

        $u1 = new User();
        $u1->setName('Alex');
        $mapper->create($u1);

        $u2 = new User();
        $u2->setName('Becky');
        $mapper->create($u2);

        $users = $mapper
            ->startQuery()
            ->fetchAll();

        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);

        $this->assertEquals(1, $users[0]->getId());
        $this->assertEquals('Alex', $users[0]->getName());

        $this->assertEquals(2, $users[1]->getId());
        $this->assertEquals('Becky', $users[1]->getName());
    }

    public function testQueriesWithCaching()
    {
        $mapper = $this->getMapper();

        $u1 = new User();
        $u1->setName('Alex');
        $mapper->create($u1);

        $u2 = new User();
        $u2->setName('Becky');
        $mapper->create($u2);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // And the logger:
        $this->conn->setLogger(new Logger());

        $query = $mapper
            ->startQuery()
            ->setCacheLength(27);
        $query->fetchAll();

        $this->assertTrue(
            $cache->contains($this->conn->createCacheKey($query->sql(), $query->params(), $query->getCacheLength()))
        );

        // Run it again:
        $users = $query->fetchAll();

        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);

        $this->assertEquals(1, $users[0]->getId());
        $this->assertEquals('Alex', $users[0]->getName());

        $this->assertEquals(2, $users[1]->getId());
        $this->assertEquals('Becky', $users[1]->getName());

        // Ensure only one query ran:
        $this->assertEquals(1, $this->conn->getLogger()->totalQueries());
    }

    public function testUpdating()
    {
        $mapper = $this->getMapper();

        $u = new User();
        $u->setName('Alex');
        $mapper->create($u);

        $u->setName('Alexander');
        $this->assertEquals($u, $mapper->update($u));

        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alexander'], $this->conn->fetch('SELECT * FROM users'));
    }

    /**
     * @expectedException           \LogicException
     * @expectedExceptionMessage    Unable to generate an update condition
     */
    public function testUpdateNoIdentifier()
    {
        $mapper = $this->getMapper();
        $u = new class {
            protected $name;

            public function getName()
            {
                return $this->name;
            }

            public function setName($name)
            {
                $this->name = $name;
                return $this;
            }
        };

        $mapper->update($u);
    }

    public function testUpdateWithTimestamps()
    {
        $u = new class extends User implements HasTimestamps
        {
            use \Solution10\Atlas\Parts\HasTimestamps;
        };
        $u->setName('Alex');

        $mapper = $this->getMapper();
        $this->assertEquals($u, $mapper->create($u));

        // Do the update:
        $u->setName('Alexander');
        $mapper->update($u);

        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alexander'], $this->conn->fetch('SELECT * FROM users'));

        $this->assertInstanceOf(\DateTime::class, $u->getCreated());
        $this->assertInstanceOf(\DateTime::class, $u->getUpdated());
    }

    public function testDelete()
    {
        $mapper = $this->getMapper();

        $u = new User();
        $u->setName('Alex');
        $mapper->create($u);

        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);

        $mapper->delete($u);

        $this->assertEquals(0, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
    }

    /**
     * @expectedException           \LogicException
     * @expectedExceptionMessage    Unable to generate a delete condition
     */
    public function testDeleteNoIdentifier()
    {
        $u = new class implements HasTimestamps{
            protected $name;

            use \Solution10\Atlas\Parts\HasTimestamps;

            public function getName()
            {
                return $this->name;
            }

            public function setName($name)
            {
                $this->name = $name;
                return $this;
            }
        };

        $mapper = $this->getMapper();
        $u->setName('Alex');
        $mapper->create($u);

        $mapper->delete($u);
    }

    public function testIsLoadedWithIdentity()
    {
        $u = new User();
        $u->setName('Alex');

        $mapper = $this->getMapper();

        $this->assertFalse($mapper->isLoaded($u));
        $mapper->create($u);
        $this->assertTrue($mapper->isLoaded($u));
    }

    public function testIsLoadedWithTimestamps()
    {
        $u = new class implements HasTimestamps{
            protected $name;

            use \Solution10\Atlas\Parts\HasTimestamps;

            public function getName()
            {
                return $this->name;
            }

            public function setName($name)
            {
                $this->name = $name;
                return $this;
            }
        };
        $u->setName('Alex');

        $mapper = $this->getMapper();

        $this->assertFalse($mapper->isLoaded($u));
        $mapper->create($u);
        $this->assertTrue($mapper->isLoaded($u));
    }

    public function testIsLoadedPlainObject()
    {
        $u = new class {
            protected $name;
        };

        $mapper = $this->getMapper();
        $this->assertFalse($mapper->isLoaded($u));
    }

    public function testSave()
    {
        $u = new User();
        $u->setName('Alex');

        $mapper = $this->getMapper();
        $this->assertEquals($u, $mapper->save($u));
        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alex'], $this->conn->fetch('SELECT * FROM users'));

        $u->setName('Alexander');
        $this->assertEquals($u, $mapper->save($u));
        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alexander'], $this->conn->fetch('SELECT * FROM users'));
    }

    public function testLoad()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Becky']);

        $u = new class extends User implements HasMapper
        {
            protected $mapper;

            public function setMapper(MapperInterface $mapper)
            {
                $this->mapper = $mapper;
                return $this;
            }

            public function getMapper(): MapperInterface
            {
                return $this->mapper;
            }
        };

        $mapper = $this->getMapper();
        $mapper->setModelInstance($u);
        $u->setMapper($mapper);

        $users = $mapper
            ->startQuery()
            ->fetchAll();

        $this->assertCount(2, $users);
        $this->assertEquals(1, $users[0]->getId());
        $this->assertEquals('Alex', $users[0]->getName());
        $this->assertEquals(2, $users[1]->getId());
        $this->assertEquals('Becky', $users[1]->getName());
    }

    public function testDatabaseMapperCRUD()
    {
        $u = new UserCRUD();
        $mapper = $this->getMapper();
        $mapper->setModelInstance($u);
        $u->setMapper($mapper);

        // Create:
        $u->setName('Alex');
        $u->save();
        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alex'], $this->conn->fetch('SELECT * FROM users'));

        // Update:
        $u->setName('Alexander');
        $u->save();
        $this->assertEquals(1, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
        $this->assertEquals(['id' => 1, 'name' => 'Alexander'], $this->conn->fetch('SELECT * FROM users'));

        // Fetch:
        $users = (new UserCRUD())
            ->setMapper($mapper)
            ->query()
            ->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals('Alexander', $users[0]->getName());

        // Delete:
        $u->delete();
        $this->assertEquals(0, $this->conn->fetch('SELECT COUNT(*) AS "aggr" FROM users')['aggr']);
    }
}
