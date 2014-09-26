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
     * query one model item
     * @param Model $item
     * @param array $extraCondition
     * @return \Guzzle\Service\Resource\Model|mixed|\PDOStatement
     */
    public function queryItem(Model $item, $extraCondition = array())
    {
        $adaptor = $this->adaptor();
        if ($adaptor instanceof Adaptor) {
            if (Debug::debug()) {
                return Debug::bench('queryItem', array($item, $extraCondition), $adaptor);
            }
            return $adaptor->queryItem($item, $extraCondition);
        }

    }


    /**
     * scan items
     * @param Model $item
     * @param $extraCondition
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function scanItems(Model $item, $extraCondition = array())
    {
        $adaptor = $this->adaptor();
        if ($adaptor instanceof Adaptor) {
            if (Debug::debug()) {
                return Debug::bench('scanItems', array($item, $extraCondition), $adaptor);
            }
            return $adaptor->scanItems($item, $extraCondition);
        }

    }


    /**
     * put one item
     * @param Model $item
     * @return \Guzzle\Service\Resource\Model|mixed
     */
    public function putItem(Model $item)
    {
        $adaptor = $this->adaptor();
        if ($adaptor instanceof Adaptor) {
            if (Debug::debug()) {
                return Debug::bench('putItem', array($item), $adaptor);
            }
            return $adaptor->putItem($item);
        }

    }


    /**
     * delete one item
     * @param Model $item
     * @return \Guzzle\Service\Resource\Model|mixed
     * @throws \CoreORM\Exception\Dao
     */
    public function deleteItem(Model $item)
    {
        $adaptor = $this->adaptor();
        if ($adaptor instanceof Adaptor) {
            if (Debug::debug()) {
                return Debug::bench('deleteItem', array($item), $adaptor);
            }
            return $adaptor->deleteItem($item);
        }

    }


    /**
     * drop table and all contents...
     * Use with care!!!
     * @param $table
     * @return \Guzzle\Service\Resource\Model
     */
    public function dropTable($table)
    {
        $adaptor = $this->adaptor();
        if ($adaptor instanceof Adaptor) {
            if (Debug::debug()) {
                return Debug::bench('dropTable', array($table), $adaptor);
            }
            return $adaptor->dropTable($table);
        }

    }


    /**
     * NOTE: API changed.
     * Read one model
     * this supports relations, just setup in models
     * @param Model $model
     * @param array $extraCondition
     */
    public function readModel(Model $model, $extraCondition = array(), $type = self::FETCH)
    {
        $item = null;
        switch ($type) {
            case self::FETCH:
                break;
            case self::SCAN:
                break;
        }

    }

}
