<?php

namespace Solution10\Data\Tests\Util;

use Solution10\Data\PHPUnit\TestCase;
use Solution10\Data\StringConverter;
use Solution10\Data\Util\Str;

class StrTest extends TestCase
{
    public function testSnakeToCamel()
    {
        /**
         * @var     StringConverter     $trait
         */
        $this->assertEquals('name', Str::snakeToCamel('name'));
        $this->assertEquals('setName', Str::snakeToCamel('name', 'set'));

        $this->assertEquals('billingAddress', Str::snakeToCamel('billing_address'));
        $this->assertEquals('setBillingAddress', Str::snakeToCamel('billing_address', 'set'));

        $this->assertEquals('setShippingAddress', Str::snakeToCamel('shipping_address', 'Set'));
    }
}
