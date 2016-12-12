<?php

namespace Solution10\Atlas\Database;

use Solution10\Atlas\MapperInterface;
use Solution10\Atlas\Results;

/**
 * Class Select
 *
 * Extended Select class that adds mapper features.
 *
 * @package     Solution10\Atlas\Database
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Select extends \Solution10\SQL\Select
{
    /**
     * @var     MapperInterface
     */
    protected $mapper;

    /**
     * @var     int
     */
    protected $cacheLength = Connection::CACHE_NEVER;

    /**
     * Sets the mapper for this query
     *
     * @var     MapperInterface     $mapper
     * @return  $this
     */
    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Returns the mapper for this query
     *
     * @return  MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return  int
     */
    public function getCacheLength(): int
    {
        return $this->cacheLength;
    }

    /**
     * @param   int     $cacheLength
     * @return  $this
     */
    public function setCacheLength(int $cacheLength)
    {
        $this->cacheLength = $cacheLength;
        return $this;
    }

    /**
     * Fetches all rows of a resultset
     *
     * @return  Results
     */
    public function fetchAll(): Results
    {
        if (!isset($this->mapper)) {
            throw new \LogicException('Mapper not set for query!');
        }
        return $this->getMapper()->fetchQuery($this);
    }

    /**
     * Fetches the first row of this query
     *
     * @return  mixed
     */
    public function fetch()
    {
        if (!isset($this->mapper)) {
            throw new \LogicException('Mapper not set for query!');
        }
        return $this->getMapper()->fetchQuery($this)->getFirst();
    }
}
