<?php

namespace Solution10\Data\Tests\Stubs;

class LoginCountEntity
{
    protected $loginCount = 0;

    public function getLoginCount()
    {
        return $this->loginCount;
    }
}
