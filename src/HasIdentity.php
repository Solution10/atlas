<?php

namespace Solution10\Atlas;

/**
 * Interface HasIdentity
 *
 * This interface declares that an object can be identified uniquely
 * within its mapper.
 *
 * @package     Solution10\Atlas
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface HasIdentity
{
    /**
     * @return  mixed
     */
    public function getId();

    /**
     * Returns the property name on the model which contains the identity.
     *
     * @return  string
     */
    public function getIdentityProperty(): string;
}
