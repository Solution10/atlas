<?php

namespace Solution10\Atlas\PHPUnit;

use Solution10\Atlas\Database\Connection;
use Solution10\Atlas\Database\ConnectionManager;

/**
 * BasicDatabase
 *
 * Sets up a basic database for the PHPUnit test cases.
 *
 * @package     Solution10\Atlas\PHPUnit
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait BasicDatabase
{
    /**
     * @var     \Solution10\Atlas\Database\Connection
     */
    protected $conn;

    /**
     * Creates the SQLite database
     */
    public function setUp()
    {
        $c = new ConnectionManager();
        $c->registerInstance();

        $connection = new Connection('sqlite::memory:');
        ConnectionManager::instance()->registerConnection('default', $connection);

        // Clear the database and create our test tables:
        $this->conn = ConnectionManager::instance()->connection('default');
        $this->conn->query('DROP TABLE IF EXISTS users');
        $this->conn->query('DROP TABLE IF EXISTS orders');

        $this->conn->query('
            CREATE TABLE `users` (
              `id` INTEGER PRIMARY KEY,
              `name` varchar(32) NOT NULL
            );
        ');

        $this->conn->query('
            CREATE TABLE `orders` (
              `id` INTEGER PRIMARY KEY,
              `user_id` INTEGER,
              `total` FLOAT
            );
        ');
    }
}
