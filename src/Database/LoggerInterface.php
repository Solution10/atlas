<?php

namespace Solution10\Atlas\Database;

/**
 * Interface LoggerInterface
 *
 * Interface that Query loggers should implement against.
 *
 * @package     Solution10\Atlas\Database
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
     * @param   float           $time
     * @return  $this
     */
    public function onQuery($sql, $parameters, $time);
}
