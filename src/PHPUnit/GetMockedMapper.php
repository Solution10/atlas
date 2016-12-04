<?php

namespace Solution10\Atlas\PHPUnit;

use Solution10\Atlas\Database\Select;
use Solution10\Atlas\MapperInterface;
use Solution10\Atlas\ReflectionPopulate;
use Solution10\Atlas\Results;

trait GetMockedMapper
{
    public function getMockedDatabaseMapper(array $data = [])
    {
        $mapper = new class implements MapperInterface
        {
            use ReflectionPopulate;

            public $items = [];

            public function save($model)
            {
                return $this->create($model);
            }

            public function create($model)
            {
                $this->items[$model->getId()] = $model;
                return $this;
            }

            public function update($model)
            {
                $this->items[$model->getId()] = $model;
                return $this;
            }

            public function delete($model)
            {
                unset($this->items[$model->getId()]);
            }

            public function load($model, array $data)
            {
                return $this->populateWithReflection($model, $data);
            }

            public function startQuery()
            {
                return (new Select())
                    ->setMapper($this)
                    ->select('*');
            }

            public function fetchQuery($query): Results
            {
                return new Results(new class {
                    public $id;
                    public $name;
                }, $this->items);
            }
        };

        $mapper->items = $data;
        return $mapper;
    }
}
