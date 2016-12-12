<?php

namespace Solution10\Data;

/**
 * Interface HasTimestamps
 *
 * Indicates that this object has created and updated timestamps
 * attached to it.
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface HasTimestamps
{
    /**
     * Sets the time that this model was created in the datastore.
     *
     * @param   mixed   $created
     * @return  $this
     */
    public function setCreated($created);

    /**
     * Returns the time at which this model was created in the datastore.
     *
     * @return  \DateTime|null
     */
    public function getCreated();

    /**
     * Sets the time that this model was updated in the datastore.
     *
     * @param   mixed   $updated
     * @return  $this
     */
    public function setUpdated($updated);

    /**
     * Returns the time at which this model was updated in the datastore.
     *
     * @return  \DateTime|null
     */
    public function getUpdated();
}
