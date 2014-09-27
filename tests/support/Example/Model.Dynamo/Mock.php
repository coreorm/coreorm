<?php
/**
 * Mock object
 * for dynamo
 */
namespace Example\Model;
use CoreORM\Model\Dynamodb;

/**
 * Class Mock
 * @package Example\Model
 */
class Mock extends Dynamodb
{
    protected $table = 'test-user-data';
    protected $fields = array(
        'id' => array(
            'type' => 'string',
            'field' => 'id',
            'required' => '1',
            'field_key' => 'id',
            'field_map' => 'id',
        ),
        'foo' => array(
            'type' => 'string',
            'field_map' => 'foo',
            'field' => 'foo',
            'field_key' => 'foo',
        ),
        'bar' => array(
            'type' => 'int',
            'field_map' => 'bar',
            'field' => 'bar',
            'field_key' => 'bar',
        ),
        'data' => array(
            'type' => 'string',
            'field_map' => 'data',
            'field' => 'data',
            'field_key' => 'data',
        ),
        'time' => array(
            'type' => 'string',
            'field_map' => 'time',
            'field' => 'time',
            'field_key' => 'time',
        ),
    );
    protected $key = array('id');
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
     * set data
     * @param mixed $value
     * @return $this
     */
    public function setData($value)
    {
        return parent::rawSetFieldData('data', $value);
    }
    /**
     * set time
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
     * @return int
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
     * @return string
     */
    public function getBar($default = null, $filter = array())
    {
        return parent::rawGetFieldData('bar', $default, $filter);
    }
    /**
     * retrieve data
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getData($default = null, $filter = array())
    {
        return parent::rawGetFieldData('data', $default, $filter);
    }
    /**
     * retrieve time
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getTime($default = null, $filter = array())
    {
        return parent::rawGetFieldData('time', $default, $filter);
    }

}
