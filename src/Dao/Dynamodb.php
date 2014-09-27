<?php
namespace CoreORM\Dao;
use CoreORM\Model\Dynamodb AS Model;
use CoreORM\Utility\Debug;
use CoreORM\Adaptor\Dynamodb AS Adaptor;

/**
 * This is the dynamodb
 * Dao - this one follows
 * NoSQL standard thus
 * there's no external objects
 * within each model
 *
 */
class Dynamodb extends Orm
{
    const FETCH = 1;
    const SCAN = 2;

    /**
     * dynamo adaptor
     * @param string $name
     * @return Adaptor
     */
    public function adaptorDynamo($name = null)
    {
        return parent::adaptor($name);

    }

    /**
     * query one model item
     * @param Model $item
     * @param array $extraCondition
     * @return \Guzzle\Service\Resource\Model|mixed|\PDOStatement
     */
    public function queryItem(Model $item, $extraCondition = array())
    {
        return $this->adaptorDynamo()->queryItem($item, $extraCondition);

    }


    /**
     * scan items
     * @param Model $item
     * @param $extraCondition
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function scanItems(Model $item, $extraCondition = array())
    {
        return $this->adaptorDynamo()->scanItems($item, $extraCondition);

    }


    /**
     * put one item
     * @param Model $item
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function putItem(Model $item)
    {
        return $this->adaptorDynamo()->putItem($item);

    }


    /**
     * delete one item
     * @param Model $item
     * @return \Guzzle\Service\Resource\Model|mixed
     * @throws \CoreORM\Exception\Dao
     */
    public function deleteItem(Model $item)
    {
        return $this->adaptorDynamo()->deleteItem($item);

    }


    /**
     * drop table and all contents...
     * Use with care!!!
     * @param $table
     * @return \Guzzle\Service\Resource\Model
     */
    public function dropTable($table)
    {
        return $this->adaptorDynamo()->dropTable($table);

    }


    /**
     * NOTE: API changed.
     * Read one model
     * @param Model $model
     * @param array $extraCondition
     * @param int $type
     */
    public function readModel(Model $model, $extraCondition = array(), $type = self::FETCH)
    {
        $item = null;
        switch ($type) {
            case self::FETCH:
                $item = $this->adaptorDynamo()->queryItem($model, $extraCondition);
                break;
            case self::SCAN:
                $item = $this->adaptorDynamo()->scanItem($model, $extraCondition);
                break;
        }

    }

}
