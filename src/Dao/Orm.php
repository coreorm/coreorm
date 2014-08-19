<?php
/**
 * this is the fully managed ORM layer of
 * the dao
 * @TODO allow maybe user defined fields in readModel(s) functions?
 */
namespace CoreORM\Dao;

use CoreORM\Model;

class Orm extends Base
{
    /**
     * Read one model
     * this supports relations, just setup in models
     * @param Model $model
     * @param bool $useSlave if true, read from slave db
     */
    public function readModel(Model $model, $useSlave = false)
    {
        // if we have a left join inside, we should not set a limit, otherwise we use 1
        $limit = 1;
        $relations = $model->activeRelations();
        if (!empty($relations)) {
            $limit = 0;
        }
        $sqlGroup = $model->composeReadSQL(null, null, null, $limit);
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
     * @param array $condition
     * @param array $bind
     * @param array $orderBy
     * @param null $limit
     * @param bool $useSlave
     * @return array
     */
    public function readModels(Model $model, $condition = array(), $bind = array(),
                               $orderBy = array(), $limit = null, $useSlave = false)
    {
        // use a soft limit to compose (using fetch row) - so no limit is to be passed in.
        $sqlGroup = $model->composeReadSQL($condition, $bind, $orderBy);
        $modelFetched = array();
        $relations = $model->activeRelations();
        $relatedModels = array();
        $class = get_class($model);
        $stmt = $this->query($sqlGroup['sql'], $sqlGroup['bind'], $useSlave);
        $limit = (int) $limit;
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
     */
    public function writeModel(Model $model)
    {
        // compose sql
        $sqlGroup = $model->composeWriteSQL();
        $this->query($sqlGroup['sql'], $sqlGroup['bind']);
        // then gave the model and id
        $this->readModel($model);

    }// end writeModel


    /**
     * delete
     * @param Model $model
     * @return \PDOStatement
     */
    public function deleteModel(Model $model)
    {
        // compose sql
        $sqlGroup = $model->composeDeleteSQL();
        return $this->query($sqlGroup['sql'], $sqlGroup['bind']);

    }// end deleteModel

}// end Orm
