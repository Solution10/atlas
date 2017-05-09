<?php

namespace Solution10\Data\Database;

use Solution10\Data\HasIdentity;
use Solution10\Data\HasTimestamps;
use Solution10\Data\MapperInterface;
use Solution10\Data\ReflectionPopulate;
use Solution10\Data\Results;
use Solution10\Pipeline\Pipeline;

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
     * @var     Pipeline[]
     */
    private $pipelines = [];

    /**
     * Returns the table name this mapper primarily operates on.
     *
     * @return  string
     */
    abstract public function getTableName();

    /**
     * Returns an instance of the main model this mapper operates on.
     *
     * @return  object
     */
    abstract public function getModelInstance();

    /**
     * Returns the connection name this mapper works with.
     *
     * @return  string
     */
    public function getConnectionName()
    {
        return 'default';
    }

    /**
     * @return  \Solution10\Data\Database\Connection
     */
    public function getConnection()
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
    abstract protected function getCreateData($model);

    /**
     * @param   object    $model
     * @return  object
     */
    public function create($model)
    {
        return $this->getCreatePipeline()->run($model);
    }

    /**
     * Returns the data for the update operation.
     *
     * @param   object  $model
     * @return  array
     */
    abstract protected function getUpdateData($model);

    /**
     * Returns the condition for the Connection object to perform the update on.
     *
     * @param   object  $model
     * @return  array
     */
    protected function getUpdateCondition($model)
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
        return $this->getUpdatePipeline()->run($model);
    }

    /**
     * Returns the delete condition for the Connection for this model
     *
     * @param   object  $model
     * @return  array
     */
    protected function getDeleteCondition($model)
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
        return $this->getDeletePipeline()->run($model);
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
        return $this->getLoadPipeline()->run($data, $model);
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
    public function fetchQuery($query)
    {
        return $this
            ->getFetchPipeline()
            ->run($query);
    }

    /**
     * Fetches a raw resultset from a query
     *
     * @param   Select  $query
     * @return  array
     */
    public function fetchQueryRaw($query)
    {
        return $this
            ->getFetchPipeline()
            ->runWithout(['data.transform'], $query);
    }

    /* ------------------- Pipelines ---------------------- */

    /**
     * @return  Pipeline
     */
    final public function getFetchPipeline()
    {
        if (!array_key_exists('fetch', $this->pipelines)) {
            $this->pipelines['fetch'] = $this->buildFetchPipeline();
        }
        return $this->pipelines['fetch'];
    }

    /**
     * @return  Pipeline
     */
    protected function buildFetchPipeline()
    {
        return (new Pipeline())
            ->step('data.read', function (Select $query) {
                return $this
                    ->getConnection()
                    ->fetchAll($query->sql(), $query->params(), $query->getCacheLength());
            })
            ->step('data.transform', function ($results) {
                return new Results($this->getModelInstance(), $results, $this);
            })
            ->step('data.hydrate', function ($results) {
                // This step does nothing by default, but you're encouraged to override it.
                return $results;
            })
        ;
    }

    /**
     * @return  Pipeline
     */
    final public function getLoadPipeline()
    {
        if (!array_key_exists('load', $this->pipelines)) {
            $this->pipelines['load'] = $this->buildLoadPipeline();
        }
        return $this->pipelines['load'];
    }

    /**
     * @return  Pipeline
     */
    protected function buildLoadPipeline()
    {
        return (new Pipeline())
            ->step('data.populate', function ($data, $model) {
                return $this->populateWithReflection($model, $data);
            })
            ->step('data.hydrate', function ($model) {
                return $model;
            })
        ;
    }

    /**
     * @return  Pipeline
     */
    final public function getCreatePipeline()
    {
        if (!array_key_exists('create', $this->pipelines)) {
            $this->pipelines['create'] = $this->buildCreatePipeline();
        }
        return $this->pipelines['create'];
    }

    /**
     * @return  Pipeline
     */
    protected function buildCreatePipeline()
    {
        return (new Pipeline())
            ->step('data.timestamps', function ($model) {
                // Handle timestamps:
                if ($model instanceof HasTimestamps) {
                    $model->setCreated(new \DateTime('now'));
                }
                return $model;
            })
            ->step('data.write', function ($model) {
                // Perform the insert:
                $iid = $this
                    ->getConnection()
                    ->insert($this->getTableName(), $this->getCreateData($model));

                if ($model instanceof HasIdentity) {
                    $ref = new \ReflectionClass($model);
                    $prop = $ref->getProperty($model->getIdentityProperty());
                    if ($prop) {
                        $prop->setAccessible(true);
                        $prop->setValue($model, $iid);
                    }
                }

                return $model;
            })
        ;
    }

    /**
     * @return  Pipeline
     */
    final public function getUpdatePipeline()
    {
        if (!array_key_exists('update', $this->pipelines)) {
            $this->pipelines['update'] = $this->buildUpdatePipeline();
        }
        return $this->pipelines['update'];
    }

    /**
     * @return  Pipeline
     */
    protected function buildUpdatePipeline()
    {
        return (new Pipeline())
            ->step('data.timestamps', function ($model) {
                // Handle timestamps:
                if ($model instanceof HasTimestamps) {
                    $model->setUpdated(new \DateTime('now'));
                }
                return $model;
            })
            ->step('data.write', function ($model) {
                // Perform the update:
                $this->getConnection()->update(
                    $this->getTableName(),
                    $this->getUpdateData($model),
                    $this->getUpdateCondition($model)
                );

                return $model;
            })
        ;
    }

    /**
     * @return  Pipeline
     */
    final public function getDeletePipeline()
    {
        if (!array_key_exists('delete', $this->pipelines)) {
            $this->pipelines['delete'] = $this->buildDeletePipeline();
        }
        return $this->pipelines['delete'];
    }

    /**
     * @return  Pipeline
     */
    protected function buildDeletePipeline()
    {
        return (new Pipeline())
            ->last('data.write', function ($model) {
                if ($this->isLoaded($model)) {
                    $this->getConnection()->delete(
                        $this->getTableName(),
                        $this->getDeleteCondition($model)
                    );
                }
                return $model;
            })
        ;
    }
}
