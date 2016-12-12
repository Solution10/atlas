<?php

namespace Solution10\Data\Tests;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\ReflectionExtract;

class ReflectionExtractTest extends TestCase
{
    /**
     * @return  ReflectionExtract
     */
    protected function getTrait()
    {
        return $this->getMockForTrait(ReflectionExtract::class);
    }

    public function testPropertyOnlyExtract()
    {
        $object = new class('Alex', 'London') {
            protected $name;
            protected $location;
            protected $shhh = "it's a secret";

            public function __construct($name, $location)
            {
                $this->name = $name;
                $this->location = $location;
            }
        };

        $trait = $this->getTrait();

        $this->assertEquals([], $trait->extractWithReflection($object, []));
        $this->assertEquals([
            'name' => 'Alex',
            'location' => 'London'
        ], $trait->extractWithReflection($object, ['name', 'location']));
    }

    public function testGetterOnlyExtract()
    {
        $object = new class('Alex', 'London') {
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
        };

        $trait = $this->getTrait();

        $this->assertEquals([], $trait->extractWithReflection($object, []));
        $this->assertEquals([
            'name' => 'Alex',
            'location' => 'London'
        ], $trait->extractWithReflection($object, ['name', 'location']));
    }
}
