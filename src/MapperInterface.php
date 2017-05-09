<?php

namespace Solution10\Data;

use React\Promise\Promise;
use Solution10\Pipeline\Pipeline;

/**
 * Interface MapperInterface
 *
 * Forms the core of the Atlas project, this controls the
 * lifespan of the objects it looks after.
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface MapperInterface
{
    /* ----------- CRUD ------------- */

    /**
     * Given a model, performs the correct action (create or update).
     *
     * @param   object   $model
     * @return  object
     */
    public function save($model);

    /**
     * Given a model, creates a new resource based on it. Will return
     * the model back with any changes applied.
     *
     * @param   object  $model
     * @return  object
     */
    public function create($model);

    /**
     * Given a model, updates the resource with any changes that have
     * been made. Returns the model back with any changes applied.
     *
     * @param   object  $model
     * @return  object
     */
    public function update($model);

    /**
     * Given a model, deletes the associated resource. Will return
     * the object, even though it no longer exists in the persistent
     * store.
     *
     * @param   object  $model
     * @return  object
     */
    public function delete($model);

    /**
     * Given a model and a dataset, populates the model with the data
     * from the dataset. Returns the hydrated model.
     *
     * @param   object  $model
     * @param   array   $data
     * @return  object
     */
    public function load($model, array $data);

    /* ------------ Chains ------------------ */

//    /**
//     * @return  Pipeline
//     */
//    public function getLoadPipeline();
//
//    /**
//     * @return  Pipeline
//     */
//    public function getCreatePipeline();
//
//    /**
//     * @return  Pipeline
//     */
//    public function getUpdatePipeline();
//
//    /**
//     * @return  Pipeline
//     */
//    public function getDeletePipeline();

    /* ----------- Querying ----------- */

    /**
     * Begins a query against this mapper.
     *
     * @return mixed
     */
    public function startQuery();

    /**
     * Given a query (usually started from startQuery()) executes
     * the query and returns the Results.
     *
     * @param   mixed   $query
     * @return  Results
     */
    public function fetchQuery($query);

    /**
     * Returns the "rawest" response possible from a given query.
     *
     * @param   mixed   $query
     * @return  mixed
     */
    public function fetchQueryRaw($query);
}
