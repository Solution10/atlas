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
        if ($created instanceof \DateTime) {
            $this->createdAt = $created;
        } else {
            if (is_integer($created)) {
                $this->createdAt = new \DateTime();
                $this->createdAt->setTimestamp($created);
            } else {
                $this->createdAt = new \DateTime($created);
            }
        }

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
        if ($updated instanceof \DateTime) {
            $this->updatedAt = $updated;
        } else {
            if (is_integer($updated)) {
                $this->updatedAt = new \DateTime();
                $this->updatedAt->setTimestamp($updated);
            } else {
                $this->updatedAt = new \DateTime($updated);
            }
        }
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
