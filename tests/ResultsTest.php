<?php

namespace Solution10\Atlas\Tests;

use Solution10\Atlas\PHPUnit\TestCase;
use Solution10\Atlas\ReflectionPopulate;
use Solution10\Atlas\Results;

class ResultsTest extends TestCase
{
    use ReflectionPopulate;

    protected function getModelInstance()
    {
        return new class
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
        };
    }

    public function testCountable()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        $this->assertCount(2, $r);
    }

    public function testArrayAccessReads()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        $this->assertTrue(isset($r[0]));
        $this->assertTrue(isset($r[1]));
        $this->assertFalse(isset($r[2]));

        $this->assertInstanceOf(get_class($m), $r[0]);
        $this->assertInstanceOf(get_class($m), $r[1]);
        $this->assertNull($r[3]);

        $this->assertEquals(1, $r[0]->getId());
        $this->assertEquals('Alex', $r[0]->getName());

        $this->assertEquals(2, $r[1]->getId());
        $this->assertEquals('Becky', $r[1]->getName());
    }

    public function testArrayAccessSettingModelInstance()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex']
        ]);

        $m2 = $this->getModelInstance();
        $this->populateWithReflection($m2, [
            'id' => 2,
            'name' => 'Becky'
        ]);

        // Fresh insert:
        $r[1] = $m2;
        $this->assertEquals($m2, $r[1]);

        // Overwrite:
        $r[0] = $m2;
        $this->assertEquals($m2, $r[0]);
    }

    public function testArrayAccessSettingArray()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex']
        ]);

        $m2 = ['id' => 2, 'name' => 'Becky'];

        // Fresh insert:
        $r[1] = $m2;
        $this->assertInstanceOf(get_class($m), $r[1]);
        $this->assertEquals(2, $r[1]->getId());
        $this->assertEquals('Becky', $r[1]->getName());

        // Overwrite:
        $r[0] = $m2;
        $this->assertInstanceOf(get_class($m), $r[0]);
        $this->assertEquals(2, $r[0]->getId());
        $this->assertEquals('Becky', $r[0]->getName());
    }
}
