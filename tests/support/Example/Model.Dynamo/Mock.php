<?php
/**
 * Mock model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model\DynamoDb;
class Mock extends Dynamodb
{
    CONST FIELD_ID = 'id';
    CONST FIELD_FOO = 'foo';
    CONST FIELD_BAR = 'bar';
    CONST FIELD_DATA = 'data';
    CONST FIELD_TIME = 'time';

    protected $table = 'test-user-data';
    protected $fields = array(
        'id' => array(
            'type' => 'string',
            'required' => true,
            'field' => 'id',
            'field_key' => 'id',
            'field_map' => 'id',
            'getter' => 'getId',
            'setter' => 'setId',
            'key_type' => 'range',
        ),
        'foo' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'foo',
            'field_key' => 'foo',
            'field_map' => 'foo',
            'getter' => 'getFoo',
            'setter' => 'setFoo',
        ),
        'bar' => array(
            'type' => 'int',
            'required' => false,
            'field' => 'bar',
            'field_key' => 'bar',
            'field_map' => 'bar',
            'getter' => 'getBar',
            'setter' => 'setBar',
        ),
        'data' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'data',
            'field_key' => 'data',
            'field_map' => 'data',
            'getter' => 'getData',
            'setter' => 'setData',
        ),
        'time' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'time',
            'field_key' => 'time',
            'field_map' => 'time',
            'getter' => 'getTime',
            'setter' => 'setTime',
        ),
    );
    protected $key = array('id');
    protected $relations = array(
    );
    
    /**
     * set Id
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        return parent::rawSetFieldData('id', $value);
    }
    /**
     * set Foo
     * @param mixed $value
     * @return $this
     */
    public function setFoo($value)
    {
        return parent::rawSetFieldData('foo', $value);
    }
    /**
     * set Bar
     * @param mixed $value
     * @return $this
     */
    public function setBar($value)
    {
        return parent::rawSetFieldData('bar', $value);
    }
    /**
     * set Data
     * @param mixed $value
     * @return $this
     */
    public function setData($value)
    {
        return parent::rawSetFieldData('data', $value);
    }
    /**
     * set Time
     * @param mixed $value
     * @return $this
     */
    public function setTime($value)
    {
        return parent::rawSetFieldData('time', $value);
    }
    
    /**
     * retrieve Id
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getId($default = null, $filter = array())
    {
        return parent::rawGetFieldData('id', $default, $filter);
    }
    /**
     * retrieve Foo
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getFoo($default = null, $filter = array())
    {
        return parent::rawGetFieldData('foo', $default, $filter);
    }
    /**
     * retrieve Bar
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getBar($default = null, $filter = array())
    {
        return parent::rawGetFieldData('bar', $default, $filter);
    }
    /**
     * retrieve Data
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getData($default = null, $filter = array())
    {
        return parent::rawGetFieldData('data', $default, $filter);
    }
    /**
     * retrieve Time
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getTime($default = null, $filter = array())
    {
        return parent::rawGetFieldData('time', $default, $filter);
    }
}