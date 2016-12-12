<?php

namespace Solution10\Data\Tests\Database;

use Doctrine\Common\Cache\ArrayCache;
use Solution10\Data\PHPUnit\BasicDatabase;
use Solution10\Data\Database\Logger;
use Solution10\SQL\Select;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use BasicDatabase;

    public function testSetGetLogger()
    {
        // Make use of the connection BasicDatabase sets up.
        $logger = new Logger();
        $this->assertNull($this->conn->getLogger());
        $this->assertEquals($this->conn, $this->conn->setLogger($logger));
        $this->assertEquals($logger, $this->conn->getLogger());
    }

    public function testGetSetCaches()
    {
        $cache = new ArrayCache();
        $this->assertEquals($this->conn, $this->conn->setCache($cache));
        $this->assertEquals($cache, $this->conn->getCache());
    }

    public function testInsert()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->assertEquals(1, $this->conn->insert('users', [
            'name' => 'Alex'
        ]));

        // verify the database:
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals(1, $users[0]['id']);
        $this->assertEquals('Alex', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(1, $events);
        $this->assertContains('INSERT INTO', $events[0]['sql']);
        $this->assertEquals(['Alex'], $events[0]['parameters']);
        $this->assertInternalType('float', $events[0]['time']);
    }

    public function testUpdate()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', ['name' => 'Alex']);

        // Do the update:
        $this->assertEquals($this->conn, $this->conn->update('users', [
            'name' => 'Alexander'
        ], ['id' => 1]));

        // verify the database
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals(1, $users[0]['id']);
        $this->assertEquals('Alexander', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(2, $events); // offset as the INSERT logs too
        $this->assertContains('UPDATE', $events[1]['sql']);
        $this->assertEquals(['Alexander', 1], $events[1]['parameters']);
        $this->assertInternalType('float', $events[1]['time']);
    }

    public function testDelete()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);

        $this->assertEquals($this->conn, $this->conn->delete('users', ['id' => 1]));

        // verify the database
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals('Lucie', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(3, $events); // offset as the INSERT logs too
        $this->assertContains('DELETE', $events[2]['sql']);
        $this->assertEquals([1], $events[2]['parameters']);
        $this->assertInternalType('float', $events[2]['time']);
    }

    public function testFetch()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);

        $users = $this->conn->fetch('SELECT * FROM users');

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetch();

        $this->assertEquals($actualUsers['id'], $users['id']);
        $this->assertEquals($actualUsers['name'], $users['name']);
    }

    public function testFetchEmpty()
    {
        $this->assertEquals([], $this->conn->fetch('SELECT * FROM users'));
    }

    public function testFetchAll()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);

        $users = $this->conn->fetchAll('SELECT * FROM users');

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetchAll();

        $this->assertCount(count($actualUsers), $users);
        $this->assertEquals($actualUsers[0]['id'], $users[0]['id']);
        $this->assertEquals($actualUsers[0]['name'], $users[0]['name']);

        $this->assertEquals($actualUsers[1]['id'], $users[1]['id']);
        $this->assertEquals($actualUsers[1]['name'], $users[1]['name']);
    }

    public function testFetchAllEmpty()
    {
        $this->assertEquals([], $this->conn->fetchAll('SELECT * FROM users'));
    }

    public function testExecuteQuery()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);
        $this->conn->insert('users', [
            'name' => 'Lucie'
        ]);

        $stmt = $this->conn->executeQuery(
            (new Select())
                ->select('*')
                ->from('users')
        );
        $users = $stmt->fetchAll();

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetchAll();

        $this->assertEquals($users, $actualUsers);
    }

    /* ------------------ Caching Query Tests ----------------------- */

    public function testFetchAllCached()
    {
        $this->conn->insert('users', ['name' => 'Alex']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = (new Select())
            ->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $results1 = $this->conn->fetchAll($query->sql(), $query->params(), 10);

        $this->assertCount(1, $results1);
        $this->assertEquals('Alex', $results1[0]['name']);

        // Verify the cache:
        $cacheKey = $this->conn->createCacheKey($query->sql(), $query->params(), 10);
        $this->assertTrue($cache->contains($cacheKey));

        $results2 = $this->conn->fetchAll($query->sql(), $query->params(), 10);
        $this->assertCount(1, $results2);
        $this->assertEquals('Alex', $results2[0]['name']);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchAllEmptyCached()
    {
        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = (new Select())
            ->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $results1 = $this->conn->fetchAll($query->sql(), $query->params(), 10);
        $this->assertCount(0, $results1);

        // Verify the cache:
        $cacheKey = $this->conn->createCacheKey($query->sql(), $query->params(), 10);
        $this->assertTrue($cache->contains($cacheKey));

        $results2 = $this->conn->fetchAll($query->sql(), $query->params(), 10);
        $this->assertCount(0, $results2);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchCached()
    {
        $this->conn->insert('users', ['name' => 'Alex']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = (new Select())
            ->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $results1 = $this->conn->fetch($query->sql(), $query->params(), 10);
        $this->assertEquals('Alex', $results1['name']);

        // Verify the cache:
        $cacheKey = $this->conn->createCacheKey($query->sql(), $query->params(), 10);
        $this->assertTrue($cache->contains($cacheKey));

        $results2 = $this->conn->fetch($query->sql(), $query->params(), 10);
        $this->assertEquals('Alex', $results2['name']);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchEmptyCached()
    {
        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = (new Select())
            ->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $results1 = $this->conn->fetch($query->sql(), $query->params(), 10);
        $this->assertEquals([], $results1);

        // Verify the cache:
        $cacheKey = $this->conn->createCacheKey($query->sql(), $query->params(), 10);
        $this->assertTrue($cache->contains($cacheKey));

        $results2 = $this->conn->fetch($query->sql(), $query->params(), 10);
        $this->assertEquals([], $results2);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }
}
