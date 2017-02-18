<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\CRUD;
use Solution10\Data\HasMapper;
use Solution10\Data\MapperInterface;

class UserCRUD extends User implements HasMapper
{
    use CRUD;

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
