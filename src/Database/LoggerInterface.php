<?php

namespace Solution10\Data\Database;

use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Interface LoggerInterface
 *
 * Interface that Query loggers should implement against.
 *
 * @package     Solution10\Data\Database
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface LoggerInterface
{
    /**
     * Sent when a query has executed
     *
     * @param   string          $sql
     * @param   array|null      $parameters
     * @param   StopwatchEvent  $event
     * @return  $this
     */
    public function onQuery($sql, $parameters, StopwatchEvent $event);
}
