<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\HasIdentity;

class User implements HasIdentity
{
    use \Solution10\Data\Parts\HasIdentity;

    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
