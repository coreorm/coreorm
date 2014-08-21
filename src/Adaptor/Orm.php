<?php
/**
 * ORM adaptor
 * this generates the sqls for CRUD on objects
 * @uses Pdo
 */
namespace CoreORM\Adaptor;
use CoreORM\Core;
use CoreORM\Exception\Adaptor;
use CoreORM\Model;
use CoreORM\Utility\Assoc;

abstract class Orm extends Pdo
{
    /**
     * get the select fields from a given model
     * @param Model $model
     * @return array
     */
    public function selectFields(Model $model)
    {
        // compose from table information itself
        $tables = array(
            $this->nameQuote($model->table()),
        );
        $condition = array();
        $pFields = $model->partialFields();
        if (!empty($pFields)) {
            // use partial fields plus the id fields
            $fields = array_merge($pFields, $model->key());
        } else {
            $fields = array_keys($model->fields());
        }
        // compose fields...
        $ArTmp = array();
        foreach ($fields as $f) {
            $info = $model->field($f);
            if (!empty($info)) {
                $ArTmp[$f] = "{$info['field_map']} AS {$info['field_key']}";
            }
        }
        // next, check the joins...
        $joins = $model->shouldJoin();
        if (!empty($joins)) {
            foreach ($joins as $table) {
                $relations = $model->relations();
                if (!empty($relations[$table]['class'])) {
                    $class = $relations[$table]['class'];
                    $obj = new $class;
                    if ($obj instanceof Model) {
                        $arResult = $this->selectFields($obj);
                        if (!empty($arResult['fields'])) {
                            $ArTmp = Assoc::merge($ArTmp, $arResult['fields']);
                        }
                    }
                    unset($obj);
                    // get table joins...
                    $join = $relations[$table]['join'];
                    $tables[] = $join;
                    // condition?
                    if (!empty($relations[$table]['condition'])) {
                        $condition[] = $relations[$table]['condition'];
                    }
                }
            }
        }
        return array(
            'fields' => $ArTmp,
            'tables' => $tables,
            'condition' => $condition
        );

    }// end selectFields


    /**
     * return a sql for reading object(s)
     * NOTE: this will return sql and bind
     * @param Model $model
     * @param array $extraCondition
     * @param array $extraBind
     * @param array $orderBy
     * @param int $limit
     * @return array
     */
    public function composeReadSQL(Model $model, $extraCondition = array(), $extraBind = array(), $orderBy = array(), $limit = 0)
    {
        $sql = 'SELECT ';
        $bind = array();
        $arTmp = $this->selectFields($model);
        $fields = $arTmp['fields'];
        $tables = $arTmp['tables'];
        $condition = $arTmp['condition'];
        $cPair = $model->getCriteriaPair();
        // figure out pair
        if (!empty($cPair)) {
            foreach ($cPair as $name => $val) {
                $condition[] = "{$name} = ?";
                $bind[] = $val;
            }
        }
        if (!empty($extraCondition) && is_array($extraCondition)) {
            $condition = array_merge($condition, $extraCondition);
        }
        if (!empty($extraBind) && is_array($extraBind)) {
            $bind = array_merge($bind, $extraBind);
        }
        // limit?
        $limit = (int) $limit;
        if (!empty($limit)) {
            $limit = ' LIMIT ' . $limit;
        } else {
            $limit = null;
        }
        // next, get relations
        $tables = implode(PHP_EOL, $tables);
        $condition = implode(' AND ' . PHP_EOL, $condition);
        // order by
        $strOrderBy = null;
        if (!empty($orderBy)) {
            $tmp = array();
            foreach ($orderBy as $field => $order) {
                $tmp[] = "{$field} {$order}";
            }
            $strOrderBy = implode(', ', $tmp);
            $strOrderBy = " ORDER BY {$strOrderBy} ";
            unset($tmp, $orderBy);
        }
        $condition = trim(($condition));
        if (!empty($condition)) {
            $condition = "WHERE {$condition}";
        }

        // start combining the sql...
        $sql .= implode(',' . PHP_EOL, $fields) . PHP_EOL .
            ' FROM ' . $tables . PHP_EOL . $condition . PHP_EOL . $strOrderBy . $limit;
        return array(
            'sql' => $sql,
            'bind' => $bind,
        );

    }// end composeReadSQL


    /**
     * compose the write sql
     * NOTE: this ones only saves itself
     * @param Model $model
     * @param string $type
     * @return array
     * @throws \CoreORM\Exception\Model
     */
    public function composeWriteSQL(Model $model, $type = Pdo::ADAPTOR_MYSQL)
    {
        $fields = array();
        $bind = array();
        foreach ($model->fields() as $field => $info) {
            if ($model->dataIsSet($field)) {
                $fName = "`{$info['field']}`";  // this quote is compatible with both sqlite3 and mysql
                if ($model->state() == Model::STATE_NEW) {
                    $fields[] = $fName;
                } else {
                    $fields[] = $fName . ' = ?';
                }
                $bind[] = $model->data($field);
            }
        }
        // compose the keys
        $table = "`{$model->table()}`";
        if ($model->state() == Model::STATE_NEW) {
            $sql = 'INSERT INTO ' . $table;
            $where = '';
        } else {
            $sql = 'UPDATE ' . $table;
            $where = array();
            foreach ($model->key() as $field) {
                if ($model->dataIsSet($field)) {
                    $fName = $model->field($field . '.field_map');
                    $where[] = $fName . ' = ?';
                    $bind[] = $model->data($field);
                }
            }
            if (empty($where)) {
                throw new \CoreORM\Exception\Model('Update SQL requires valid primary key');
            }
            $where = ' WHERE ' . implode(' AND ', $where);
            if ($type == Pdo::ADAPTOR_MYSQL) {
                $where .= ' LIMIT 1';
            }
        }
        if ($model->state() == Model::STATE_NEW) {
            // use prepared field set
            $values = array();
            $cnt = count($fields);
            for ($i = 0; $i < $cnt; $i ++) {
                $values[] = '?';
            }
            $sql .= PHP_EOL . '(' . implode(', ', $fields) .
                ') VALUES (' . implode(', ', $values) . ')';
        } else {
            $sql .= PHP_EOL . ' SET ' . implode(', ' . PHP_EOL, $fields) . $where;
        }
        return array(
            'sql' => $sql,
            'bind' => $bind,
        );

    }// end composeWriteSQL


    /**
     * compose the deletion sql here
     * @param Model $model
     * @param string $type
     * @return array
     * @throws \CoreORM\Exception\Model
     */
    public function composeDeleteSQL(Model $model, $type = Pdo::ADAPTOR_MYSQL)
    {
        $bind = array();
        $sql = "DELETE FROM `" . $model->table() . "`";
        $where = array();
        foreach ($model->key() as $field) {
            if ($model->dataIsSet($field)) {
                $fName = $model->field($field . '.field_map');
                $where[] = $fName . ' = ?';
                $bind[] = $model->data($field);
            }
        }
        if (empty($where)) {
            throw new \CoreORM\Exception\Model('Update SQL requires valid primary key');
        }
        $sql .= ' WHERE ' . implode(' AND ', $where);
        if ($type == Pdo::ADAPTOR_MYSQL) {
            // since sqlite doesn't support limit in delete out of the box
            $sql .= ' LIMIT 1';
        }
        return array(
            'sql' => $sql,
            'bind' => $bind,
        );

    }// end composeDeleteSQL

}