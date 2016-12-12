<?php

namespace Solution10\Data\Tests;

use Solution10\Data\HasMapper;
use Solution10\Data\MapperInterface;
use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\ReflectionPopulate;
use Solution10\Data\Results;

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

    /* ----------- Countable Tests ------------- */

    public function testCountable()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        $this->assertCount(2, $r);
    }

    /* --------- ArrayAccess Tests ------------- */

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

    public function testArrayAccessUnsetWithoutGetting()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        unset($r[0]);
        $this->assertCount(1, $r);
        $this->assertFalse(isset($r[0]));
        $this->assertEquals(2, $r[1]->getId());
        $this->assertEquals('Becky', $r[1]->getName());
    }

    public function testArrayAccessUnsetWithGetting()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        // Trigger a get first:
        $r[0];

        // Now unset and make sure it behaves properly:
        unset($r[0]);
        $this->assertCount(1, $r);
        $this->assertFalse(isset($r[0]));
        $this->assertEquals(2, $r[1]->getId());
        $this->assertEquals('Becky', $r[1]->getName());
    }

    /* ------------ Iterator Tests ---------- */

    public function testIterator()
    {
        $d = [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ];
        $m = $this->getModelInstance();
        $r = new Results($m, $d);

        $loops = 0;
        foreach ($r as $i => $result) {
            $this->assertInstanceOf(get_class($m), $result);
            $this->assertEquals($d[$i]['id'], $result->getId());
            $this->assertEquals($d[$i]['name'], $result->getName());
            $loops ++;
        }

        $this->assertEquals(2, $loops);
    }

    /* ---------- Other Tests ---------------- */

    public function testGetFirst()
    {
        $m = $this->getModelInstance();
        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        $first = $r->getFirst();
        $this->assertInstanceOf(get_class($m), $first);
        $this->assertEquals(1, $first->getId());
        $this->assertEquals('Alex', $first->getName());

        $r = new Results($m, []);
        $first = $r->getFirst();
        $this->assertNull($first);
    }

    public function testPopulateWithMapper()
    {
        $m = new class implements HasMapper
        {
            protected $data = [];

            public function getId()
            {
                return $this->data['id'];
            }

            public function setId($id)
            {
                $this->data['id'] = $id;
                return $this;
            }

            public function getName()
            {
                return $this->data['name'];
            }

            public function setName($name)
            {
                $this->data['name'] = $name;
                return $this;
            }

            public function getMapper(): MapperInterface
            {
                // We only care about load() in this example.
                return new class implements MapperInterface
                {
                    public function save($model)
                    {
                    }

                    public function create($model)
                    {
                    }

                    public function update($model)
                    {
                    }

                    public function delete($model)
                    {
                    }

                    public function load($model, array $data)
                    {
                        $model->setName($data['name']);
                        $model->setId($data['id']);
                        return $model;
                    }

                    public function startQuery()
                    {
                    }

                    public function fetchQuery($query): Results
                    {
                    }
                };
            }
        };

        $r = new Results($m, [
            ['id' => 1, 'name' => 'Alex'],
            ['id' => 2, 'name' => 'Becky']
        ]);

        $this->assertEquals(1, $r[0]->getId());
        $this->assertEquals('Alex', $r[0]->getName());

        $this->assertEquals(2, $r[1]->getId());
        $this->assertEquals('Becky', $r[1]->getName());
    }
}
