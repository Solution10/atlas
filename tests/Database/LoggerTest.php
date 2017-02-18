<?php

namespace Solution10\Data\Tests\Database;

use Solution10\Data\Database\Logger;
use Solution10\Data\Tests\Stubs\MockStopwatchEvent;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    protected function getDummyStopwatchEvent($totalTime)
    {
        $event = new MockStopwatchEvent(0);
        $event->hardDuration = $totalTime;
        return $event;
    }

    public function testOnEvent()
    {
        $l = new Logger();
        $this->assertEquals($l, $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            $this->getDummyStopwatchEvent(10)
        ));
    }

    public function testTotalQueries()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            $this->getDummyStopwatchEvent(10)
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            $this->getDummyStopwatchEvent(10)
        );

        $this->assertEquals(2, $l->totalQueries());
    }

    public function testTotalTime()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            $this->getDummyStopwatchEvent(10)
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            $this->getDummyStopwatchEvent(20)
        );

        $this->assertEquals(30, $l->totalTime());
    }

    public function testEvents()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            $this->getDummyStopwatchEvent(10)
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            $this->getDummyStopwatchEvent(20)
        );

        $this->assertEquals([
            [
                'sql' => 'SELECT * FROM users WHERE id = ?',
                'parameters' => [1],
                'time' => 10
            ],
            [
                'sql' => 'SELECT * FROM users WHERE id = ?',
                'parameters' => [2],
                'time' => 20
            ],
        ], $l->events());
    }
}
