<?php

namespace Solution10\Data\Tests;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\ReflectionPopulate;

class ReflectionPopulateTest extends TestCase
{
    /**
     * @return  ReflectionPopulate
     */
    protected function getTrait()
    {
        return $this->getMockForTrait(ReflectionPopulate::class);
    }

    protected function getObject()
    {
        return (new class {
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
        });
    }

    public function testPopulateKnownProperties()
    {
        $object = $this->getObject();

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, [
            'id' => 27,
            'name' => 'Alex'
        ]);

        $this->assertEquals(27, $object->getId());
        $this->assertEquals('Alex', $object->getName());
    }

    public function testPopulateUnknownProperties()
    {
        $object = $this->getObject();
        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, [
            'age' => 28,
            'location' => 'Toronto'
        ]);

        $this->assertNull($object->getId());
        $this->assertNull($object->getName());
        $this->assertFalse(property_exists($object, 'age'));
        $this->assertFalse(property_exists($object, 'location'));
    }

    public function testPopulateWithSetter()
    {
        $object = new class {
            private $data = [];

            public function getName()
            {
                return $this->data['name'];
            }

            public function setName(string $name)
            {
                $this->data['name'] = 'Hello '.$name;
                return $this;
            }
        };

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, ['name' => 'Alex']);

        $this->assertEquals('Hello Alex', $object->getName());
    }

    public function testPopulatePrefersSetterToProperty()
    {
        $object = new class {
            protected $name;

            public function getName()
            {
                return $this->name;
            }

            public function setName(string $name)
            {
                $this->name = 'Hello '.$name;
                return $this;
            }
        };

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, ['name' => 'Alex']);

        $this->assertEquals('Hello Alex', $object->getName());
    }
}
