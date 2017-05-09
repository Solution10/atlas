<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\Database\DatabaseMapper;
use Solution10\Data\ReflectionExtract;

class MockUsersDatabaseMapper extends DatabaseMapper
{
    use ReflectionExtract;

    protected $modelInstance;

    public function getTableName()
    {
        return 'users';
    }

    public function setModelInstance($model)
    {
        $this->modelInstance = $model;
        return $this;
    }

    public function getModelInstance()
    {
        return (isset($this->modelInstance)) ? $this->modelInstance : new User();
    }

    protected function getCreateData($model)
    {
        return $this->extractWithReflection($model, ['name']);
    }

    protected function getUpdateData($model)
    {
        return $this->extractWithReflection($model, ['name']);
    }
}
