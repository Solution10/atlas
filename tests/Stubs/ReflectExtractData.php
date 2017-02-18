<?php

namespace Solution10\Data\Tests\Stubs;

class ReflectExtractData
{
    protected $data = [];

    public function __construct($name, $location)
    {
        $this->data['name'] = $name;
        $this->data['location'] = $location;
        $this->data['shhh'] = "it's a secret";
    }

    public function getName()
    {
        return $this->data['name'];
    }

    public function getLocation()
    {
        return $this->data['location'];
    }

    public function getSecret()
    {
        return $this->data['shhh'];
    }
}
