<?php

namespace Solution10\Data\PHPUnit;

use Solution10\Data\Tests\Stubs\MockDatabaseMapper;

trait GetMockedMapper
{
    public function getMockedDatabaseMapper(array $data = [])
    {
        return new MockDatabaseMapper($data);
    }
}
