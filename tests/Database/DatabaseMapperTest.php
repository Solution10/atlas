<?php

namespace Solution10\Data\Tests\Database;

use Doctrine\Common\Cache\ArrayCache;
use Solution10\Data\Database\Logger;
use Solution10\Data\PHPUnit\BasicDatabase;
use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\Tests\Stubs\MockEntity;
use Solution10\Data\Tests\Stubs\MockUsersDatabaseMapper;
use Solution10\Data\Tests\Stubs\User;
use Solution10\Data\Tests\Stubs\UserCRUD;
use Solution10\Data\Tests\Stubs\UserWithMapper;
use Solution10\Data\Tests\Stubs\UserWithTimestamps;

class DatabaseMapperTest extends TestCase
{
    use BasicDatabase;

    /**
     * @return  MockUsersDatabaseMapper
     */
    protected function getMapper()
    {
        return new MockUsersDatabaseMapper();
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
        $u = new UserWithTimestamps();
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

    public function testFetchQueryRaw()
    {
        $mapper = $this->getMapper();
        $usersCount = $mapper->startQuery()->count();
        $this->assertEquals(0, $usersCount);

        $u1 = new User();
        $u1->setName('Alex');
        $mapper->create($u1);

        $u2 = new User();
        $u2->setName('Becky');
        $mapper->create($u2);

        $usersCount = $mapper->startQuery()->count();
        $this->assertEquals(2, $usersCount);
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
        $u = new MockEntity();

        $mapper->update($u);
    }

    public function testUpdateWithTimestamps()
    {
        $u = new UserWithTimestamps();
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
        $u = new MockEntity();

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
        $u = new MockEntity();
        $u->setName('Alex');

        $mapper = $this->getMapper();

        $this->assertFalse($mapper->isLoaded($u));
        $mapper->create($u);
        $this->assertTrue($mapper->isLoaded($u));
    }

    public function testIsLoadedPlainObject()
    {
        $u = (object)[
            'name' => null,
        ];

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

        $u = new UserWithMapper();

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
