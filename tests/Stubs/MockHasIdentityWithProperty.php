<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\Parts\HasIdentity;

class MockHasIdentityWithProperty
{
    use HasIdentity;

    public function getIdentityProperty()
    {
        return 'my_id';
    }
}
