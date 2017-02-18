<?php

namespace Solution10\Data\Tests\Stubs;

use Symfony\Component\Stopwatch\StopwatchEvent;

class MockStopwatchEvent extends StopwatchEvent
{
    public $hardDuration;

    public function getDuration()
    {
        if (isset($this->hardDuration)) {
            return $this->hardDuration;
        }
        return parent::getDuration();
    }
}
