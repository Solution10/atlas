<?php

namespace Solution10\Data\Parts;

/**
 * Class HasTimestamps
 *
 * Implements the HasTimestamps interface to provide created and
 * updated timestamps on a model.
 *
 * @package     Solution10\Data\Parts
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait HasTimestamps
{
    use GetDateTime;

    /**
     * @var     \DateTime
     */
    protected $createdAt = null;

    /**
     * @var     \DateTime
     */
    protected $updatedAt = null;

    /**
     * Sets the time that this model was created in the datastore.
     *
     * @param   mixed   $created
     * @return  $this
     */
    public function setCreated($created)
    {
        $this->createdAt = $this->getDateTimeFrom($created);
        return $this;
    }

    /**
     * Returns the time at which this model was created in the datastore.
     *
     * @return  \DateTime|null
     */
    public function getCreated()
    {
        return $this->createdAt;
    }

    /**
     * Sets the time that this model was updated in the datastore.
     *
     * @param   mixed   $updated
     * @return  $this
     */
    public function setUpdated($updated)
    {
        $this->updatedAt = $this->getDateTimeFrom($updated);
        return $this;
    }

    /**
     * Returns the time at which this model was updated in the datastore.
     *
     * @return  \DateTime|null
     */
    public function getUpdated()
    {
        return $this->updatedAt;
    }
}
