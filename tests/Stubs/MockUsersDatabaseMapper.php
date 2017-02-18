<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\Database\DatabaseMapper;

class MockUsersDatabaseMapper extends DatabaseMapper
{
    protected $modelInstance;

    public function getTableName()
    {
        return 'users';
    }

    public function getConnectionName()
    {
        return 'default';
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
        return [
            'name' => $model->getName()
        ];
    }

    protected function getUpdateData($model)
    {
        return [
            'name' => $model->getName()
        ];
    }
}
