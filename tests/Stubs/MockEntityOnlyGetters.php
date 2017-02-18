<?php

namespace Solution10\Data\Tests\Stubs;

class MockEntityOnlyGetters
{
    protected $id;
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
