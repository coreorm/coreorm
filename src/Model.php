<?php
/**
 * the base model
 * for managed data tables
 * and interrelations
 * NOTE: all db Model should be generated outside
 */
namespace CoreORM;

use CoreORM\Adaptor\Pdo;
use CoreORM\Exception\Model as ModelException;
use CoreORM\Utility\Assoc;
use CoreORM\Utility\Date;

class Model
{
    // states
    const STATE_NEW = 1;
    const STATE_READ = 2;
    // relations
    const RELATION_SINGLE = 1;
    const RELATION_MULTI = 2;

    /**
     * table name
     * @var string
     */
    protected $table = null;

    /**
     * fields
     * in format of
     * array(
    tableName_fieldName:array(
     *          'type':int/str/object/etc,
     *          'required':true/false (from NOT NULL),
     *          'field':fieldName,
     *          'setter':setFieldName
     *          'getter':getFieldName
     *      )
     * )
     * @var array
     */
    protected $fields = array();

    /**
     * primary key(s)
     * in format of
     * array(
     *     const_field_name
     * )
     * @var array
     */
    protected $key = array();

    /**
     * relations
     * @see README.md
     * @var array
     */
    protected $relations = array();

    /**
     * the actual data
     * that stores everything
     * @var array
     */
    protected $data = array();

    /**
     * what should we join
     * @var array
     */
    protected $shouldJoin = array();

    /**
     * what fields to select
     * @var array
     */
    protected $partialFields = array();

    /**
     * the state of current object
     * @var int
     */
    protected $state = self::STATE_NEW;


    /**
     * constructor
     * NOTE: set up from constructor will make the object
     * in read state (as we use this to setup the rows)
     * @param $data
     * @param int $state
     */
    public function __construct($data = array(), $state = null)
    {
        if (!empty($data) && !empty($state)) {
            $this->rawSetUp($data, $state);
        }

    }// end __construct


    /**
     * set up data and state
     * @param $data
     * @param $state
     * @return $this
     */
    public function rawSetUp($data, $state)
    {
        foreach ($data as $field => $val) {
            $this->rawSetFieldData($field, $val);
        }
        if (!empty($state)) {
            $this->state = $state;
        }
        return $this;

    }// end rawSetUp


    /**
     * set field data
     * according to the data
     * type
     * @param $field
     * @param $val
     * @return $this
     * @throws \CoreORM\Exception\Model
     */
    public function rawSetFieldData($field, $val)
    {
        // must be verified
        if (empty($this->fields[$field])) {
            return $this;
        }
        /*
         * tableName_fieldName:array(
               'type':int/str/object/etc,
               'required':true/false (from NOT NULL),
               'field':fieldName,
               'setter':setFieldName
               'getter':getFieldName
           )
         */
        $meta = $this->fields[$field];
        $type = $meta['type'];
        // set by type
        switch ($type) {
            case 'string':
            case 'datetime':
                $val = (string) $val;
                break;
            case 'int':
                $val = (int) $val;
                break;
            case 'float':
                $val = (float) $val;
                break;
            case 'array':
                $val = (array) $val;
                break;
            default:
                if (class_exists($type)) {
                    // then we're expecting an object!!!
                    if (!$val instanceof $type) {
                        throw new ModelException('Set value error in ' . __class__ .
                            '. Type of [' . $field . '] is expected to be [' .
                            $type . '].');
                    }
                }
        }
        // set at the end
        $this->data[$field] = $val;
        return $this;

    }// end rawSetFieldData


    /**
     * get the raw data - note this is NOT formatted, it's plain data in the db.
     * @param $name
     * @param null $default
     * @param array $filter filter over the content
     * @return float|int|mixed|null
     */
    protected function rawGetFieldData($name, $default = null, $filter = array())
    {
        // must be verified
        if (empty($this->fields[$name])) {
            return $this;
        }
        $val = Assoc::get($this->data, $name, $default);
        // filter: to string value only
        if (!empty($filter)) {
            foreach ($filter as $method) {
                // if array - use array as object, method
                if (is_array($method)) {
                    if (method_exists($method[0], $method[1])) {
                        $val = call_user_func_array($method, array($val));
                    }
                } else {
                    // direct access to single function
                    if (function_exists($method)) {
                        $val = $method($val);
                    }
                    // otherwise if it's :: - class function
                    if (strpos($method, '::') !== false) {
                        $tmp = explode('::', $method);
                        if (method_exists($tmp[0], $tmp[1])) {
                            $val = call_user_func_array($tmp, array($val));
                        }
                    }
                }
            }
        }
        // continue with val
        if (empty($val) && !empty($default)) {
            $val = $default;
        }
        // avoid null input...
        if (empty($val)) {
            $meta = $this->fields[$name];
            $type = $meta['type'];
            // make default out
            switch ($type) {
                case 'int':
                    $val = 0;
                    break;
                case 'float':
                    $val = 0.00;
                    break;
                case 'array':
                    $val = array();
                    break;
                case 'date':
                case 'datetime':
                    $val = trim($val);
                    break;
                default:
                    if (class_exists($type)) {
                        $val = new $type;
                    }
            }
        }
        return $val;

    }// end rawGetFieldData


    /**
     * format the date (use it for mysql date)
     * @param $name
     * @param string $format
     * @param null $default
     * @return string
     */
    protected function formatDateByName($name, $format = 'js F, Y H:i', $default = null)
    {
        $val = $this->rawGetFieldData($name);
        if ($format == false) {
            // just return the raw form
            return $val;
        }
        return Date::formatDatetime($val, $format, $default);

    }// end formatDateByName


    /**
     * add one object into an array
     * @param Model $object
     * @param $name
     * @return $this
     */
    protected function addOneModel(Model $object, $name)
    {
        return $this->addOneItem($name, $object, $object->primaryKey(true));

    }// end addOneModel


    /**
     * this retrieves a single model out
     * @param $name
     * @param $key
     * @param string $className
     * @return Model
     */
    protected function getOneModel($name, $key, $className = '\CoreORM\Model')
    {
        $list = (array) $this->rawGetFieldData($name, array());
        $model = Assoc::get($list, $key);
        if (empty($model) && class_exists($className)) {
            $model = new $className;
        }
        return $model;

    }// end getOneModel


    /**
     * add one item into an array
     * @param $name
     * @param $value
     * @param null $key
     * @return $this
     */
    protected function addOneItem($name, $value, $key = null)
    {
        // do $name add if not already...
        $vars = !empty($this->data[$name]) ? $this->data[$name] : array();
        // add to it...
        $vars[$key] = $value;
        // set it back in
        $this->data[$name] = $vars;
        // unset vars
        unset($vars);
        // enable chain...
        return $this;

    }// end addOneItem

/*------------------- getters -------------------*/

    /**
     * get the id of the object
     *
     * @param bool $flat if true, return a flat str id for special use
     *
     * @return mixed $id the id
     */
    public function primaryKey($flat = false)
    {
        // return array if not flat, or string if it is.
        $keyData = array();
        $val     = null;
        foreach ($this->key as $keyField) {
            $keyData[$keyField] = $val = $this->rawGetFieldData($keyField);
        }
        // now, if only 1, return current one
        if (count($keyData) == 1 && $flat) {
            return $val;
        }
        if ($flat) {
            return implode('_', $keyData);
        }
        return $keyData;

    }// end getID


    /**
     * the table name
     * @return string
     */
    public function table()
    {
        return $this->table;

    }// end table


    /**
     * get table fields
     * @return array
     */
    public function fields()
    {
        return $this->fields;

    }// end fields


    /**
     * field info
     * @param $name
     * @return mixed
     */
    public function field($name)
    {
        return Assoc::get($this->fields, $name);

    }// end field


    /**
     * get relations
     * @return array
     */
    public function relations()
    {
        return $this->relations;

    }// end relations


    /**
     * fetch only active relations
     * @return array
     */
    public function activeRelations()
    {
        $tmp = array();
        foreach ($this->shouldJoin as $table) {
            if (!empty($this->relations[$table])) {
                $tmp[$table] = $this->relations[$table];
            }
        }
        return $tmp;

    }// end activeRelations


    /**
     * get a single relation information out
     * @param $table
     * @return mixed
     */
    public function relation($table)
    {
        return Assoc::get($this->relations, $table);

    }// end relation


    /**
     * set or retrieve state
     * @param $state
     * @return mixed
     */
    public function state($state = null)
    {
        if ($state == self::STATE_NEW || $state == self::STATE_READ) {
            $this->state = $state;
        }
        return $this->state;

    }// end state

/*--------------------------------------[cloning]--------------------------------------*/

    /**
     * create mutable object clone
     * which contains only the id and status
     * for partial updates of the tables
     * NOTE: only works with functions that links back to rawSetFieldData
     * @param bool $updateOriginal
     * @return mixed
     * @throws Exception\Model
     */
    public function cloneMutable($updateOriginal = false)
    {
        // this works only if id is not empty
        $id = $this->primaryKey();
        if (empty($id)) {
            throw new \CoreORM\Exception\Model('Unable to clone the object for updates, as there is no valid ID');
        }
        // otherwise...
        $class = get_class($this);
        $clone = new $class;;
        if ($clone instanceof Model) {
            foreach ($id as $k => $v) {
                $clone->rawSetFieldData($k, $v);
            }
            // we also setup the primary key
            $clone->state(self::STATE_READ);
            // if update original
            if ($updateOriginal) {
                $clone->placeOriginalObject($this);
            }
        }
        return $clone;

    }// end cloneMutable


    /**
     * place the original object into
     * the mutable object so we can
     * update both
     * @param Model $obj
     * @return $this
     */
    public function placeOriginalObject(Model $obj)
    {
        $this->parentObject = $obj;
        return $this;

    }// end placeOriginalObject


    /**
     * the external setvar
     * @see rawSetFieldData($name, $val)
     */
    public function syncCloneDataToOriginal($name, $val)
    {
        return $this->rawSetFieldData($name, $val);

    }// end syncCloneDataToOriginal

/*--------------------------------------[flatten]--------------------------------------*/

    /**
     * get table data out
     * @param bool $withRelations
     * @param array $filters - format: array(field => call back function)
     * @return array
     */
    public function toArray($withRelations = false, $filters = array())
    {
        // 1st of all, just internal ones
        $export = array();
        $data   = $this->data;
        foreach ($this->fields as $field => $info) {
            if (isset($data[$field])) {
                $v = $this->rawGetFieldData($field);
                $fieldMap = $info['field_map'];
                if (!empty($filters[$fieldMap])) {
                    $filter = $filters[$fieldMap];
                    $v = call_user_func_array($filter, array($v));
                }
                $export[$info['field']] = $v;
                unset($data[$field]);
            }
        }
        // next, if external - this will also get passed on to sub models
        if ($withRelations) {
            foreach ($data as $key => $val) {
                if ($val instanceof Model) {
                    $export[$key] = $val->toArray($withRelations, $filters);
                }
                if (is_array($val)) {
                    // we only ever loop one level
                    foreach ($val as $k => $v) {
                        if ($v instanceof Model) {
                            $export[$key][$k] = $v->toArray($withRelations, $filters);
                        } else {
                            $export[$key][$k] = $v;
                        }
                    }
                }
            }
        }
        unset($data);
        return $export;

    }// end toArray


    /**
     * export to json
     * @param bool $withRelations
     * @param array $filters
     * @return string
     */
    public function toJson($withRelations = false, $filters = array())
    {
        return json_encode($this->toArray($withRelations, $filters));

    }// end toJson


    /**
     * export to xml (Experimental)
     * @return mixed
     * @throws Exception\Model
     */
    public function toXML()
    {
        $export = array();
        $data   = $this->data;
        foreach ($this->fields as $field => $info) {
            if (isset($data[$field])) {
                $tag   = $info['field'];
                $value = $this->rawGetFieldData($field);
                if (is_string($value)) {
                    $value = "<![CDATA[$value]]>";
                }
                $export[$tag] = "<$tag>$value</$tag>";
            }
        }
        unset($data);
        // compose
        $xml       = implode('', $export);
        $parentTag = $this->table;
        $xml       = "<$parentTag>$xml</$parentTag>";
        return '<?xml version="1.0" encoding="UTF-8"?>' . $xml;

    }// end toXML

/*--------------------------------------[ SQL ]--------------------------------------*/

    /**
     * add join to the object
     * @param Model $model
     * @return $this|array
     */
    public function shouldJoin(Model $model = null)
    {
        // default return should joins
        if (empty($model)) {
            return $this->shouldJoin;
        }
        // otherwise set
        $tableName = $model->table();
        $this->shouldJoin[$tableName] = $tableName;
        return $this;

    }// end join


    /**
     * add join to the object
     * @return $this
     */
    public function shouldJoinAll()
    {
        $this->shouldJoin = array_keys($this->relations);
        return $this;

    }// end join


    /**
     * clear joins
     * @return $this
     */
    public function clearJoin()
    {
        $this->shouldJoin = array();
        return $this;

    }// end clearJoin


    /**
     * partially select fields
     * @param $arrayFields
     * @return $this
     */
    public function partialSelect($arrayFields)
    {
        // fields need to be converted to fieldnames (under score)
        foreach ($arrayFields as &$field) {
            $field = str_replace(array('`', '.'), array('', '_'), $field);
        }
        // just set, verification can be done later
        $this->partialFields = $arrayFields;
        return $this;

    }// end partialSelect


    /**
     * get current partial fields
     * @return array
     */
    public function partialFields()
    {
        return $this->partialFields;

    }// end partialFields


    /**
     * get the key fields
     * @return array
     */
    public function key()
    {
        return $this->key;

    }// end key


    /**
     * get the data
     * @param str $key if null, return all
     * @return array
     */
    public function data($key = null)
    {
        if (empty($key)) {
            return $this->data;
        }
        if (!empty($this->data[$key])) {
            return $this->data[$key];
        }

    }// end data


    /**
     * is the field set in data?
     * @param $field
     * @return bool
     */
    public function dataIsSet($field)
    {
        return isset($this->data[$field]);

    }// end dataIsSet


    /**
     * get current criteria by
     * object settings
     * @return array array(condition, bind)
     */
    public function getCriteriaPair()
    {
        $where = array();
        if ($this->primaryKey(true)) {
            // we use primary key - ignore others.
            foreach ($this->key as $field) {
                // get the var...
                $fName = $this->fields[$field]['field_map'];
                $value = $this->rawGetFieldData($field);
                $where[$fName] = $value;
            }
        } else {
            // check all fields that are NOT null (isset)
            foreach ($this->fields as $field => $info) {
                if (isset($this->data[$field])) {
                    $fName = $this->fields[$field]['field_map'];
                    $where[$fName] = $this->data[$field];
                }
            }
        }
        return $where;

    }// end getCriteriaPair


    /**
     * join another model at
     * runtime
     * @param string $join LEFT RIGHT INNER
     * @param Model $model
     * @param array $on in format of array(left id => right id)
     * @param int $type
     * @param string $condition
     * @return $this
     */
    public function join($join, Model $model, $on, $type = self::RELATION_SINGLE, $condition = null)
    {
        $onTmp = array();
        $table = $this->table;
        $rTable = $model->table();
        foreach ($on as $left => $right) {
            $onTmp[] = "`{$table}`.`{$left}` = `{$rTable}`.`{$right}`";
        }
        $on = implode(' AND ', $onTmp);
        unset($onTmp);
        $this->relations[$model->table()] = array(
            'class' => get_class($model),
            'type' => $type,
            'join' => "' {$join} JOIN `{$rTable}` ON {$on} '",
            'condition' => $condition
        );
        return $this;

    }// end join


    /**
     * inner join
     * @param Model $model
     * @param $on
     * @param int $type
     * @param null $condition
     * @return $this
     */
    public function innerJoin(Model $model, $on, $type = self::RELATION_SINGLE, $condition = null)
    {
        return $this->join('INNER', $model, $on, $type, $condition);

    }// end innerJoin


    /**
     * right join
     * @param Model $model
     * @param $on
     * @param int $type
     * @param null $condition
     * @return $this
     */
    public function rightJoin(Model $model, $on, $type = self::RELATION_SINGLE, $condition = null)
    {
        return $this->join('RIGHT', $model, $on, $type, $condition);

    }// end rightJoin


    /**
     * left join
     * @param Model $model
     * @param $on
     * @param int $type
     * @param null $condition
     * @return $this
     */
    public function leftJoin(Model $model, $on, $type = self::RELATION_SINGLE, $condition = null)
    {
        return $this->join('LEFT', $model, $on, $type, $condition);

    }// end leftJoin


    /**
     * get the join type
     * @param $table
     * @return int
     */
    public function joinType($table)
    {
        return Assoc::get($this->relations, $table . '.type');

    }// end joinType

}// end Base
