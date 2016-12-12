<?php

namespace Solution10\Data;

/**
 * Class CRUD
 *
 * A helper trait to give ActiveRecord style crud abilities
 * to a model class. In no way required.
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait CRUD
{
    /**
     * @return  MapperInterface
     */
    abstract public function getMapper(): MapperInterface;

    /**
     * Saves this model via the mapper.
     *
     * @return object
     */
    public function save()
    {
        return $this->getMapper()->save($this);
    }

    /**
     * Deletes this model via the mapper.
     *
     * @return object
     */
    public function delete()
    {
        return $this->getMapper()->delete($this);
    }

    /**
     * Begin a query against the mapper.
     *
     * @return mixed
     */
    public function query()
    {
        return $this->getMapper()->startQuery();
    }
}
