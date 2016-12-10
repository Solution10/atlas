<?php

namespace Solution10\Atlas\Tests\Parts;

use Solution10\Atlas\Parts\HasTimestamps;
use Solution10\Atlas\PHPUnit\TestCase;

class HasTimestampsTest extends TestCase
{
    /**
     * @return  HasTimestamps
     */
    protected function getTrait()
    {
        return $this->getMockForTrait(HasTimestamps::class);
    }

    public function testDefaults()
    {
        $t = $this->getTrait();
        $this->assertNull($t->getCreated());
        $this->assertNull($t->getUpdated());
    }

    public function testBasicSetGetCreated()
    {
        $t = $this->getTrait();
        $dt = new \DateTime();

        $this->assertEquals($t, $t->setCreated($dt));
        $this->assertEquals($dt, $t->getCreated());
    }

    public function testStringSetCreated()
    {
        $t = $this->getTrait();
        $created = '2016-12-06 07:47:27 +01:00';

        $t->setCreated($created);
        $this->assertInstanceOf(\DateTime::class, $t->getCreated());
        $this->assertEquals('2016-12-06T07:47:27+01:00', $t->getCreated()->format('c'));
    }

    public function testBasicSetGetUpdated()
    {
        $t = $this->getTrait();
        $dt = new \DateTime();

        $this->assertEquals($t, $t->setUpdated($dt));
        $this->assertEquals($dt, $t->getUpdated());
    }
}
