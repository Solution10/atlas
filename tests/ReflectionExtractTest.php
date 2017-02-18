<?php

namespace Solution10\Data\Tests;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\ReflectionExtract;
use Solution10\Data\Tests\Stubs\ReflectExtractData;
use Solution10\Data\Tests\Stubs\ReflectExtractProperties;

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
        $object = new ReflectExtractProperties('Alex', 'London');

        $trait = $this->getTrait();

        $this->assertEquals([], $trait->extractWithReflection($object, []));
        $this->assertEquals([
            'name' => 'Alex',
            'location' => 'London'
        ], $trait->extractWithReflection($object, ['name', 'location']));
    }

    public function testGetterOnlyExtract()
    {
        $object = new ReflectExtractData('Alex', 'London');

        $trait = $this->getTrait();

        $this->assertEquals([], $trait->extractWithReflection($object, []));
        $this->assertEquals([
            'name' => 'Alex',
            'location' => 'London'
        ], $trait->extractWithReflection($object, ['name', 'location']));
    }
}
