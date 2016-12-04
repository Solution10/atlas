<?php

namespace Solution10\Atlas;

/**
 * Interface HasMapper
 *
 * Declares that this object has a mapper attached to it.
 *
 * @package     Solution10\Atlas
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
