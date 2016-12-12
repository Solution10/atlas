<?php

namespace Solution10\Data\Tests\Database;

use Solution10\Data\Database\Connection;
use Solution10\Data\Database\Select;
use Solution10\Data\PHPUnit\GetMockedMapper;
use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\Results;

class SelectTest extends TestCase
{
    use GetMockedMapper;

    public function testSetGetMapper()
    {
        $mapper = $this->getMockedDatabaseMapper();

        $s = new Select();
        $this->assertNull($s->getMapper());
        $this->assertEquals($s, $s->setMapper($mapper));
        $this->assertEquals($mapper, $s->getMapper());
    }

    public function testSetGetCacheLength()
    {
        $s = new Select();
        $this->assertEquals(Connection::CACHE_NEVER, $s->getCacheLength());
        $this->assertEquals($s, $s->setCacheLength(27));
        $this->assertEquals(27, $s->getCacheLength());
        $this->assertEquals($s, $s->setCacheLength(Connection::CACHE_FOREVER));
        $this->assertEquals(Connection::CACHE_FOREVER, $s->getCacheLength());
    }

    /**
     * @expectedException           \LogicException
     * @expectedExceptionMessage    Mapper not set for query!
     */
    public function testFetchAllNoMapper()
    {
        $s = new Select();
        $s->fetchAll();
    }

    /**
     * @expectedException           \LogicException
     * @expectedExceptionMessage    Mapper not set for query!
     */
    public function testFetchNoMapper()
    {
        $s = new Select();
        $s->fetch();
    }

    public function testFetchAll()
    {
        $mapper = $this->getMockedDatabaseMapper([
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Beth'],
            ['id' => 3, 'name' => 'Chris']
        ]);

        $s = new Select();
        $s->setMapper($mapper);
        $results = $s->fetchAll();

        $this->assertInstanceOf(Results::class, $results);
        $this->assertCount(3, $results);
        $this->assertEquals('Alex', $results[0]->name);
        $this->assertEquals('Beth', $results[1]->name);
        $this->assertEquals('Chris', $results[2]->name);
    }

    public function testFetch()
    {
        $mapper = $this->getMockedDatabaseMapper([
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Beth'],
            ['id' => 3, 'name' => 'Chris']
        ]);

        $s = new Select();
        $s->setMapper($mapper);
        $result = $s->fetch();

        $this->assertInternalType('object', $result);
        $this->assertEquals('Alex', $result->name);
    }
}
