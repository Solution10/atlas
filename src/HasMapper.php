<?php

namespace Solution10\Data;

/**
 * Interface HasMapper
 *
 * Declares that this object has a mapper attached to it.
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface HasMapper
{
    /**
     * @return  MapperInterface
     */
    public function getMapper(): MapperInterface;
}
