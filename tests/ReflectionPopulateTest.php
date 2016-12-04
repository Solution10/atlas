<?php

namespace Solution10\Atlas\Tests;

use Solution10\Atlas\PHPUnit\TestCase;
use Solution10\Atlas\ReflectionPopulate;

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
}
