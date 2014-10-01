<?php
/**
 * this is the fully managed ORM layer of
 * the dao
 * @TODO allow maybe user defined fields in readModel(s) functions?
 */
namespace CoreORM\Dao;

use CoreORM\Model, CoreORM\Adaptor\Dynamodb AS Adaptor, CoreORM\Model\Dynamodb AS DModel;
use CoreORM\Utility\Assoc;
use PhpParser\Node\Expr\BinaryOp\Mod;

class Orm extends Base
{
    // dynamo fetch modes
    const FETCH_MODEL_QUERY = 1;
    const FETCH_MODEL_SCAN  = 2;

    /**
     * Read one model
     * this supports relations, just setup in models
     * @param Model $model
     * @param array $option
     * @throws \CoreORM\Exception\Dao
     */
    public function readModel(Model $model, $option = array(
            'useSlave' => false,
            'condition' => array(),
            'bind' => array(),
            'fetchMode' => self::FETCH_MODEL_QUERY
        ))
    {
        $condition = Assoc::get($option, 'condition');
        // check for Dynamo
        if ($model instanceof DModel) {
            $fetchMode = Assoc::get($option, 'fetchMode', self::FETCH_MODEL_QUERY);
            return $this->readDynamoModel($model, $condition, $fetchMode);
        }
        // below are relation db
        $useSlave = Assoc::get($option, 'useSlave', false);
        $bind = Assoc::get($option, 'bind', array());
        // if we have a left join inside, we should not set a limit, otherwise we use 1
        $limit = 1;
        $relations = $model->activeRelations();
        if (!empty($relations)) {
            $limit = 0;
        }
        $sqlGroup = $this->adaptor()->composeReadSQL($model, $condition, $bind, null, $limit);
        $result = $this->fetchAll($sqlGroup['sql'], $sqlGroup['bind'], $useSlave);
        $modelFetched = false;
        $relatedModels = array();
        foreach ($result as $row) {
            if (!$modelFetched) {
                $model->rawSetUp($row, Model::STATE_READ);
                $modelFetched = true;
            }
            // next, with each relation...
            foreach ($relations as $table => $info) {
                $class = $info['class'];
                $setter = $info['setter'];
                if ($info['type'] == Model::RELATION_SINGLE) {
                    if (empty($relatedModels[$table])) {
                        $tmp = new $class($row, Model::STATE_READ);
                        if ($tmp instanceof Model && $tmp->primaryKey(true)) {
                            $model->$setter($tmp);
                            $relatedModels[$table] = true;
                            unset($tmp);
                        }
                    }
                } else {
                    // multiple instance, we just keep adding to it...
                    $tmp = new $class($row, Model::STATE_READ);
                    if ($tmp instanceof Model && $tmp->primaryKey(true)) {
                        $model->$setter($tmp);
                        unset($tmp);
                    }
                }
            }
        }

    }// end readModel


    /**
     * Read multiple model
     * this supports relations, just setup in models
     * by default, if this joins any model the multiple children option will be on.
     * @param Model $model
     * @param array $option
     * @throws \CoreORM\Exception\Dao
     * @internal param array $condition
     * @internal param array $bind
     * @internal param array $orderBy
     * @internal param null $limit
     * @internal param bool $useSlave
     * @return array
     */
    public function readModels(Model $model, $option = array(
            // relation db options
            'condition' => array(),
            'bind' => array(),
            'orderBy' => array(),
            'limit' => null,
            'useSlave' => false,
            // dynamo options
            'fetchMode' => self::FETCH_MODEL_SCAN
        ))
    {
        $condition = Assoc::get($option, 'condition', array());
        // dynamo
        if ($model instanceof DModel) {
            // read models must be scan - unless you insist
            $fetchMode = Assoc::get($option, 'fetchMode', self::FETCH_MODEL_SCAN);
            return $this->readDynamoModels($model, $condition, $fetchMode);
        }
        // relational DB
        $bind = Assoc::get($option, 'bind', array());
        $orderBy = Assoc::get($option, 'orderBy', array());
        $limit = (int) Assoc::get($option, 'limit', 0);
        $useSlave = Assoc::get($option, 'useSlave', false);
        // use a soft limit to compose (using fetch row) - so no limit is to be passed in.
        $sqlGroup = $this->adaptor()->composeReadSQL($model, $condition, $bind, $orderBy);
        $modelFetched = array();
        $relations = $model->activeRelations();
        $relatedModels = array();
        $class = get_class($model);
        $stmt = $this->query($sqlGroup['sql'], $sqlGroup['bind'], $useSlave);
        $cnt = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // fetch the main model
            $m = new $class;
            if ($m instanceof Model) {
                $m->rawSetUp($row, Model::STATE_READ);
            }
            $pk = $m->primaryKey(true);
            // fetch models
            if (empty($modelFetched[$pk])) {
                $modelFetched[$pk] = $m;
                // also count plus 1 until reaches limit
                if ($limit > 0) {
                    $cnt ++;
                    if ($cnt >= $limit) {
                        // stop fetching more...
                        break;
                    }
                }
            } else {
                $m = $modelFetched[$pk];
            }
            // next, with each relation...
            foreach ($relations as $table => $info) {
                $rClass = $info['class'];
                $setter = $info['setter'];
                if ($info['type'] == Model::RELATION_SINGLE) {
                    if (empty($relatedModels[$pk][$table])) {
                        $tmp = new $rClass;
                        if ($tmp instanceof Model) {
                            $tmp->rawSetUp($row, Model::STATE_READ);
                            if ($tmp->primaryKey(true)) {
                                $m->$setter($tmp);
                                $relatedModels[$pk][$table] = true;
                            }
                        }
                        unset($tmp);
                    }
                } else {
                    // multiple instance, we just keep adding to it...
                    $tmp = new $rClass;
                    if ($tmp instanceof Model) {
                        $tmp->rawSetUp($row, Model::STATE_READ);
                        $tpk = $tmp->primaryKey(true);
                        if (!empty($tpk)) {
                            $m->$setter($tmp);
                        }
                    }
                    unset($tmp);
                }
            }
        }
        return $modelFetched;

    }// end readModel


    /**
     * NOTE: for security/performance concern
     * we DO NOT allow chain save of the sub models,
     * so this only saves the model itself
     * @param Model $model
     * @param array $option
     * @return \CoreORM\Model|\PDOStatement|void
     * @throws \CoreORM\Exception\Dao
     */
    public function writeModel(Model $model)
    {
        // shift to dynamo if model is dynamo
        if ($model instanceof DModel) {
            if ($model->state() == Model::STATE_NEW) {
                // insert a new dynamo object
                return $this->putItem($model);
            }
            // otherwise, update
            return $this->updateItem($model);
        }
        // compose sql
        $sqlGroup = $this->adaptor()->composeWriteSQL($model, $this->adaptor()->getType());
        $this->query($sqlGroup['sql'], $sqlGroup['bind']);
        // then gave the model and id
        $this->readModel($model);
        return $model;

    }// end writeModel


    /**
     * write a bunch of models
     * this requires the models to be
     * a list of valid Models
     * @param $models
     * @param int $batchSize this determines the batch size, if 0, DO NOT use batch
     * If batchsize is bigger than 0, the given models will NOT be inflated with data
     * as it's impossible to do so.
     * @param bool $allowException
     * @return array
     * @throws \Exception
     */
    public function writeModels($models, $batchSize = 1, $allowException = false)
    {
        $results = array();
        $batchSize = (int) $batchSize;
        if ($batchSize <= 1) {
            foreach ($models as $model) {
                if ($model instanceof Model) {
                    try {
                        $this->writeModel($model);
                        $results[$model->primaryKey(true)] = array('success' => true);
                    } catch (\Exception $e) {
                        if ($allowException) {
                            $results[$model->primaryKey(true)] = array(
                                'success' => false,
                                'error' => $e->getMessage(),
                                'details' => $e->getTraceAsString()
                            );
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            // finally, return the results back (but at the same time, models are inflated)
            return $results;
        }
        // is it dynamo?
        $model = current($models);
        if ($model instanceof DModel) {
            return $this->putItems($models, $batchSize, $allowException);
        }
        // otherwise, do batch write/insert as in one sql composed.
        $results = array();
        $queries = array();
        $i = 0;
        foreach ($models as $model) {
            $i ++;
            $sqlGroup = $this->adaptor()->composeWriteSQL($model, $this->adaptor()->getType());
            $sql = $this->interpolateQuery($sqlGroup['sql'], $sqlGroup['bind']) . ';';
            $k = (int) ceil($i / $batchSize) - 1;
            $queries[$k][] = $sql;
        }
        foreach ($queries as $k => $sqlGroup) {
            $sql = implode(PHP_EOL, $sqlGroup);
            // run and get result out
            try {
                $this->query($sql);
                $results[$k] = array('success' => true);
            } catch (\Exception $e) {
                if ($allowException) {
                    $results[$k] = array(
                        'success' => false,
                        'error' => $e->getMessage(),
                        'details' => $e->getTraceAsString()
                    );
                } else {
                    throw $e;
                }
            }
        }
        return $results;

    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    public function interpolateQuery($query, $params)
    {
        $keys = array();
        # build a regular expression for each parameter
        foreach ($params as $key => &$value) {
            if (is_string($key)) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }
            $value = $this->adaptor()->quote($value);
        }
        $query = preg_replace($keys, $params, $query, 1, $count);
        return $query;
    }


    /**
     * delete
     * @param Model $model
     * @param array $option
     * @throws \CoreORM\Exception\Dao
     * @return \PDOStatement
     */
    public function deleteModel(Model $model, $option = array(
            'condition' => array()
        ))
    {
        // dynamo
        if ($model instanceof DModel) {
            return $this->adaptorDynamo()->deleteItem($model, Assoc::get($option, 'condition', array()));
        }
        // compose sql
        $sqlGroup = $this->adaptor()->composeDeleteSQL($model, $this->adaptor()->getType());
        return $this->query($sqlGroup['sql'], $sqlGroup['bind']);

    }// end deleteModel


    /*---------------[dynamo specific functions]-----------------*/

    /**
     * dynamo adaptor
     * @param string $name
     * @return Adaptor
     */
    public function adaptorDynamo($name = null)
    {
        return parent::adaptor($name);

    }// end adaptorDynamo

    /**
     * query one model item
     * @param DModel $item
     * @param array $condition
     * @return \Guzzle\Service\Resource\Model|mixed|\PDOStatement
     */
    public function queryItem(DModel $item, $condition = array())
    {
        return $this->adaptorDynamo()->queryItem($item, $condition);

    }// end queryItem


    /**
     * scan items
     * @param DModel $item
     * @param $condition
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function scanItems(DModel $item, $condition = array())
    {
        return $this->adaptorDynamo()->scanItems($item, $condition);

    }// end scanItems


    /**
     * put one item
     * @param DModel $item
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function putItem(DModel $item)
    {
        return $this->adaptorDynamo()->putItem($item);

    }


    /**
     * update one item
     * @param DModel $item
     * @return \PDOStatement|void
     */
    public function updateItem(DModel $item)
    {
        return $this->adaptorDynamo()->updateItem($item);

    }


    /**
     * delete one item
     * @param DModel $item
     * @return \Guzzle\Service\Resource\Model|mixed
     * @throws \CoreORM\Exception\Dao
     */
    public function deleteItem(DModel $item)
    {
        return $this->adaptorDynamo()->deleteItem($item);

    }


    /**
     * drop table and all contents...
     * Use with care!!!
     * @param $table
     * @param bool $waitTillFinish
     * @return \Guzzle\Service\Resource\Model
     */
    public function dropTable($table, $waitTillFinish = false)
    {
        return $this->adaptorDynamo()->dropTable($table, $waitTillFinish);

    }


    /**
     * create table if not exist
     * @param $schema
     * @param bool $waitTillFinished
     * @return bool
     * @throws \Exception
     */
    public function createTableIfNotExists($schema, $waitTillFinished = true)
    {
        return $this->adaptorDynamo()->createTableIfNotExists($schema, $waitTillFinished);

    }


    /**
     * create table
     * @param $schema
     * @param bool $waitTillFinished
     * @return bool
     */
    public function createTable($schema, $waitTillFinished = true)
    {
        return $this->adaptorDynamo()->createTable($schema, $waitTillFinished);

    }

    /*---------------[dynamo object functions]-----------------*/

    /**
     * internal API
     * read dynamo model only
     * @param DModel $model
     * @param array $condition
     * @param $type
     * @return DModel
     */
    protected function readDynamoModel(DModel $model, $condition = array(), $type = self::FETCH)
    {
        $item = null;
        switch ($type) {
            case self::FETCH_MODEL_QUERY:
                $item = $this->adaptorDynamo()->queryItem($model, $condition);
                break;
            case self::FETCH_MODEL_SCAN:
                $item = $this->adaptorDynamo()->scanItems($model, $condition);
                break;
        }
        if (empty($item)) {
            return null;
        }
        if ($item->get('Count') <= 0) {
            return null;
        }
        $row = (array) current($item->get('Items'));
        foreach ($row as $field => $val) {
            if (!is_array($val)) {
                continue;
            }
            $model->rawSetFieldData($field, current($val));
        }
        return $model;

    }

    /**
     * internal API
     * read dynamo models only
     * @param DModel $model
     * @param array $condition
     * @param $type
     * @return DModel
     */
    protected function readDynamoModels(DModel $model, $condition = array(), $type = self::FETCH)
    {
        $item = null;
        switch ($type) {
            case self::FETCH_MODEL_QUERY:
                $item = $this->adaptorDynamo()->queryItem($model, $condition);
                break;
            case self::FETCH_MODEL_SCAN:
                $item = $this->adaptorDynamo()->scanItems($model, $condition);
                break;
        }
        if (empty($item)) {
            return null;
        }
        $items = $item->get('Items');
        if (empty($items)) {
            return array();
        }
        if ($item->get('Count') <= 0) {
            return null;
        }
        $data = array();
        $class = get_class($model);
        foreach ($items as $row) {
            $model = new $class;
            foreach ($row as $field => $val) {
                $model->rawSetFieldData($field, current($val));
            }
            $data[$model->primaryKey(true)] = $model;
        }
        return $data;

    }

    /**
     * write a bunch of models
     * this requires the models to be
     * a list of valid Models
     * @param $models
     * @param int $batchSize this determines the batch size, max should be 25 (configurable)
     * @param bool $allowException
     * @return array
     * @throws \Exception
     */
    public function putItems($models, $batchSize, $allowException = false)
    {
        // prepare batch - note the 25 limit
        $results = array();
        $queries = array();
        $i = 0;
        foreach ($models as $model) {
            if ($model instanceof DModel) {
                $i ++;
                $k = (int) ceil($i / $batchSize) - 1;
                $data = $model->toArray(false);
                $queries[$k]['RequestItems'][$model->table()][] = array(
                    'PutRequest' => array(
                        'Item' => $this->adaptorDynamo()->formatAttributes($data)
                    )
                );
            }
        }
        foreach ($queries as $k => $item) {
            if (!empty($item)) {
                try {
                    $results[$k] = array(
                        'success' => true,
                        'result' => $this->adaptorDynamo()->putItems($item)
                    );
                } catch (\Exception $e) {
                    if ($allowException) {
                        $results[$k] = array(
                            'success' => false,
                            'error' => $e->getMessage(),
                            'details' => $e->getTraceAsString()
                        );
                    } else {
                        throw $e;
                    }
                }
            }
        }
        return $results;

    }


}// end Orm
