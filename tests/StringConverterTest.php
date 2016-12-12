<?php

namespace Solution10\Data\Tests;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\StringConverter;

class StringConverterTest extends TestCase
{
    public function testSnakeToCamel()
    {
        /**
         * @var     StringConverter     $trait
         */
        $trait = $this->getMockForTrait(StringConverter::class);
        $this->assertEquals('name', $trait->snakeToCamel('name'));
        $this->assertEquals('setName', $trait->snakeToCamel('name', 'set'));

        $this->assertEquals('billingAddress', $trait->snakeToCamel('billing_address'));
        $this->assertEquals('setBillingAddress', $trait->snakeToCamel('billing_address', 'set'));

        $this->assertEquals('setShippingAddress', $trait->snakeToCamel('shipping_address', 'Set'));
    }
}
