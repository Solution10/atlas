<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\HasMapper;
use Solution10\Data\MapperInterface;

class UserWithMapper extends User implements HasMapper
{
    protected $mapper;

    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}
