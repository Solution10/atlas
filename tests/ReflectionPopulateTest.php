<?php

namespace Solution10\Data\Tests;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\ReflectionPopulate;
use Solution10\Data\Tests\Stubs\LoginCountEntity;
use Solution10\Data\Tests\Stubs\MockEntityOnlyGetters;
use Solution10\Data\Tests\Stubs\UserSpecialSet;

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
        return new MockEntityOnlyGetters();
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
        $object = new UserSpecialSet();

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, ['name' => 'Alex']);

        $this->assertEquals('Hello Alex', $object->getName());
    }

    public function testPopulatePrefersSetterToProperty()
    {
        $object = new UserSpecialSet();

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, ['name' => 'Alex']);

        $this->assertEquals('Hello Alex', $object->getName());
    }

    public function testSnakeProperties()
    {
        $object = new LoginCountEntity();

        $trait = $this->getTrait();
        $object = $trait->populateWithReflection($object, ['login_count' => 27]);

        $this->assertEquals(27, $object->getLoginCount());
    }
}
