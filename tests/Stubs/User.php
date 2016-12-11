<?php

namespace Solution10\Atlas\Tests\Stubs;

use Solution10\Atlas\HasIdentity;

class User implements HasIdentity
{
    use \Solution10\Atlas\Parts\HasIdentity;

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
