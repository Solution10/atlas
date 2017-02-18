<?php

namespace Solution10\Data\Database;

use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Logger
 *
 * Implementation of the Logger interface for capturing queries sent through the S10 ORM.
 *
 * @package     Solution10\Data\Database
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Logger implements LoggerInterface
{
    /**
     * @var     array
     */
    protected $events = [];

    /**
     * Sent when a query has executed
     *
     * @param   string          $sql
     * @param   array|null      $parameters
     * @param   StopwatchEvent  $event
     * @return  $this
     */
    public function onQuery($sql, $parameters, StopwatchEvent $event = null)
    {
        $this->events[] = [
            'sql' => $sql,
            'parameters' => $parameters,
            'time' => ($event)? $event->getDuration() : 0,
        ];
        return $this;
    }

    /**
     * Returns the total number of queries executed
     *
     * @return  int
     */
    public function totalQueries()
    {
        return count($this->events);
    }

    /**
     * Returns the total time taken by all events (ms)
     *
     * @return  float
     */
    public function totalTime()
    {
        $total = 0.0;
        foreach ($this->events as $e) {
            $total += $e['time'];
        }
        return $total;
    }

    /**
     * Returns all the events we've put in
     *
     * @return  array
     */
    public function events()
    {
        return $this->events;
    }
}
