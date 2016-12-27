<?php

namespace Solution10\Data\Database;

use Solution10\Data\HasIdentity;
use Solution10\Data\HasTimestamps;
use Solution10\Data\MapperInterface;
use Solution10\Data\ReflectionPopulate;
use Solution10\Data\Results;

/**
 * Class DatabaseMapper
 *
 * Abstract base class for a database mapper object.
 *
 * @package     Solution10\Data\Database
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
abstract class DatabaseMapper implements MapperInterface
{
    use ReflectionPopulate;

    /**
     * Returns the table name this mapper primarily operates on.
     *
     * @return  string
     */
    abstract public function getTableName(): string;

    /**
     * Returns the connection name this mapper works with.
     *
     * @return  string
     */
    abstract public function getConnectionName(): string;

    /**
     * Returns an instance of the main model this mapper operates on.
     *
     * @return  object
     */
    abstract public function getModelInstance();

    /**
     * @return  \Solution10\Data\Database\Connection
     */
    public function getConnection(): Connection
    {
        return ConnectionManager::instance()->connection($this->getConnectionName());
    }

    /**
     * Returns whether this model has been loaded or not from the datastore.
     *
     * @param   object  $model
     * @return  bool
     */
    public function isLoaded($model)
    {
        // Check for HasIdentity:
        if ($model instanceof HasIdentity) {
            return $model->getId() !== null;
        }

        // We can also check the created timestamp, if it exists:
        if ($model instanceof HasTimestamps) {
            return $model->getCreated() !== null;
        }

        return false;
    }

    /**
     * Saves a user model, either create or update based on the model state.
     *
     * @param   object    $model
     * @return  object
     */
    public function save($model)
    {
        return ($this->isLoaded($model))? $this->update($model) : $this->create($model);
    }

    /**
     * Returns the create data for the given model.
     *
     * @param   object  $model
     * @return  array
     */
    abstract protected function getCreateData($model): array;

    /**
     * @param   object    $model
     * @return  object
     */
    public function create($model)
    {
        if ($model instanceof HasTimestamps) {
            $model->setCreated(new \DateTime('now'));
        }

        $iid = $this->getConnection()->insert($this->getTableName(), $this->getCreateData($model));

        if ($model instanceof HasIdentity) {
            $ref = new \ReflectionClass($model);
            $prop = $ref->getProperty($model->getIdentityProperty());
            if ($prop) {
                $prop->setAccessible(true);
                $prop->setValue($model, $iid);
            }
        }

        return $model;
    }

    /**
     * Returns the data for the update operation.
     *
     * @param   object  $model
     * @return  array
     */
    abstract protected function getUpdateData($model): array;

    /**
     * Returns the condition for the Connection object to perform the update on.
     *
     * @param   object  $model
     * @return  array
     */
    protected function getUpdateCondition($model): array
    {
        if ($model instanceof HasIdentity) {
            return [$model->getIdentityProperty() => $model->getId()];
        }

        throw new \LogicException(
            'Unable to generate an update condition. Please override getUpdateCondition in '.self::class
        );
    }

    /**
     * @param   object    $model
     * @return  object
     */
    public function update($model)
    {
        if ($model instanceof HasTimestamps) {
            $model->setUpdated(new \DateTime('now'));
        }

        $this->getConnection()->update(
            $this->getTableName(),
            $this->getUpdateData($model),
            $this->getUpdateCondition($model)
        );

        return $model;
    }

    /**
     * Returns the delete condition for the Connection for this model
     *
     * @param   object  $model
     * @return  array
     */
    protected function getDeleteCondition($model): array
    {
        if ($model instanceof HasIdentity) {
            return [$model->getIdentityProperty() => $model->getId()];
        }

        throw new \LogicException(
            'Unable to generate a delete condition. Please override getDeleteCondition in '.self::class
        );
    }

    /**
     * Deletes the given model
     *
     * @param   object    $model
     * @return  $this
     */
    public function delete($model)
    {
        if ($this->isLoaded($model)) {
            $this->getConnection()->delete(
                $this->getTableName(),
                $this->getDeleteCondition($model)
            );
        }
        return $this;
    }

    /**
     * Loads an item from its database representation.
     *
     * @param   object    $model
     * @param   array   $data
     * @return  object
     */
    public function load($model, array $data)
    {
        return $this->populateWithReflection($model, $data);
    }

    /**
     * @return  Select
     */
    public function startQuery()
    {
        $conn = $this->getConnection();
        $q = new Select($conn->dialect());
        $q
            ->select($this->getTableName().'.*')
            ->from($this->getTableName())
            ->setMapper($this)
        ;
        return $q;
    }

    /**
     * Runs a given query against the datastore and returns the result in
     * a Resultset object
     *
     * @param   Select  $query
     * @return  Results
     */
    public function fetchQuery($query): Results
    {
        $data = $this->getConnection()->fetchAll($query->sql(), $query->params(), $query->getCacheLength());
        return new Results($this->getModelInstance(), $data);
    }

    /**
     * Fetches a raw resultset from a query
     *
     * @param   Select  $query
     * @return  array
     */
    public function fetchQueryRaw($query)
    {
        $data = $this->getConnection()->fetchAll($query->sql(), $query->params(), $query->getCacheLength());
        return $data;
    }
}
