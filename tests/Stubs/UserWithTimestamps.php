<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\HasTimestamps;

class UserWithTimestamps extends User implements HasTimestamps
{
    use \Solution10\Data\Parts\HasTimestamps;
}
