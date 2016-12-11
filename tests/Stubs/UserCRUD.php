<?php

namespace Solution10\Atlas\Tests\Stubs;

use Solution10\Atlas\CRUD;
use Solution10\Atlas\HasMapper;
use Solution10\Atlas\MapperInterface;

class UserCRUD extends User implements HasMapper
{
    use CRUD;

    protected $mapper;

    public function setMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }
}
