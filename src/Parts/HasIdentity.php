<?php

namespace Solution10\Data\Parts;

/**
 * Class HasIdentity
 *
 * Implements the HasIdentity interface in the most straightfoward
 * way possible.
 *
 * @package     Solution10\Data\Parts
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait HasIdentity
{
    /**
     * @var     mixed
     */
    protected $id;

    /**
     * @return  mixed
     */
    public function getId()
    {
        return $this->{$this->getIdentityProperty()};
    }

    /**
     * Returns the property name on the model which contains the identity.
     *
     * @return  string
     */
    public function getIdentityProperty()
    {
        return 'id';
    }
}
