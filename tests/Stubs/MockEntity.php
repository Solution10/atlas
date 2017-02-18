<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\HasTimestamps;

class MockEntity implements HasTimestamps
{
    protected $name;

    use \Solution10\Data\Parts\HasTimestamps;

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
