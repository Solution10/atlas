<?php

namespace Solution10\Data\Tests\Stubs;

class ReflectExtractProperties
{
    protected $name;
    protected $location;
    protected $shhh = "it's a secret";

    public function __construct($name, $location)
    {
        $this->name = $name;
        $this->location = $location;
    }
}
