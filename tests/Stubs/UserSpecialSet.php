<?php

namespace Solution10\Data\Tests\Stubs;

class UserSpecialSet
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = 'Hello '.$name;
        return $this;
    }
}
