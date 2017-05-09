<?php

namespace Solution10\Data\Tests\Stubs;

use Solution10\Data\Database\Select;
use Solution10\Data\MapperInterface;
use Solution10\Data\ReflectionPopulate;
use Solution10\Data\Results;
use Solution10\Data\WorkflowChain;

class MockDatabaseMapper implements MapperInterface
{
    use ReflectionPopulate;

    public $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

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

    public function fetchQuery($query)
    {
        return new Results(new SimpleEntity(), $this->items);
    }

    public function fetchQueryRaw($query)
    {
        return $this->items;
    }
}
