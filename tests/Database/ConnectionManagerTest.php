<?php

namespace Solution10\Data\Tests\Database;

use Solution10\Data\Database\Connection;
use Solution10\Data\Database\ConnectionManager;

class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceCreates()
    {
        $c = new ConnectionManager();
        $c->registerInstance();

        $i = ConnectionManager::instance();
        $this->assertInstanceOf(ConnectionManager::class, $i);
    }

    public function testRegisteringConnections()
    {
        $i = new ConnectionManager();

        $connection = new Connection('sqlite::memory:');
        $this->assertEquals($i, $i->registerConnection('default', $connection));
        $this->assertTrue(in_array('default', array_keys($i->registeredConnections())));
    }

    public function testBuildingConnections()
    {
        $i = new ConnectionManager();

        $connection = new Connection('sqlite::memory:');
        $i->registerConnection('default', $connection);

        $conn = $i->connection('default');
        $this->assertEquals($connection, $conn);
    }

    public function testConnectionReuse()
    {
        $i = new ConnectionManager();

        $i->registerConnection('default', new Connection('sqlite::memory:'));

        $conn = $i->connection('default');
        $conn->mark = 'green';
        $conn2 = $i->connection('default');
        $this->assertEquals($conn, $conn2);
        $this->assertEquals('green', $conn2->mark);
    }

    /**
     * @expectedException       \Solution10\Data\Database\Exception\ConnectionException
     * @expectedExceptionCode   \Solution10\Data\Database\Exception\ConnectionException::UNKNOWN_CONNECTION
     */
    public function testUnknownConnection()
    {
        $i = new ConnectionManager();
        $i->connection('unknown');
    }
}
