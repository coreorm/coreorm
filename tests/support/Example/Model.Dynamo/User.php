<?php
/**
 * User model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model\DynamoDb;
class User extends Dynamodb
{
    CONST FIELD_ID = 'id';
    CONST FIELD_NAME = 'name';
    CONST FIELD_ADDRESS = 'address';
    CONST FIELD_CREATED_AT = 'created_at';

    protected $table = 'test-user';
    protected $fields = array(
        'id' => array(
            'type' => 'int',
            'required' => true,
            'field' => 'id',
            'field_key' => 'id',
            'field_map' => 'id',
            'getter' => 'getId',
            'setter' => 'setId',
            'key_type' => 'hash',
        ),
        'name' => array(
            'type' => 'string',
            'required' => true,
            'field' => 'name',
            'field_key' => 'name',
            'field_map' => 'name',
            'getter' => 'getName',
            'setter' => 'setName',
            'key_type' => 'range',
        ),
        'address' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'address',
            'field_key' => 'address',
            'field_map' => 'address',
            'getter' => 'getAddress',
            'setter' => 'setAddress',
        ),
        'created_at' => array(
            'type' => 'int',
            'required' => false,
            'field' => 'created_at',
            'field_key' => 'created_at',
            'field_map' => 'created_at',
            'getter' => 'getCreatedAt',
            'setter' => 'setCreatedAt',
        ),
    );
    protected $key = array('id', 'name');
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
     * set Name
     * @param mixed $value
     * @return $this
     */
    public function setName($value)
    {
        return parent::rawSetFieldData('name', $value);
    }
    /**
     * set Address
     * @param mixed $value
     * @return $this
     */
    public function setAddress($value)
    {
        return parent::rawSetFieldData('address', $value);
    }
    /**
     * set CreatedAt
     * @param mixed $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        return parent::rawSetFieldData('created_at', $value);
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
     * retrieve Name
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getName($default = null, $filter = array())
    {
        return parent::rawGetFieldData('name', $default, $filter);
    }
    /**
     * retrieve Address
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getAddress($default = null, $filter = array())
    {
        return parent::rawGetFieldData('address', $default, $filter);
    }
    /**
     * retrieve CreatedAt
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getCreatedAt($default = null, $filter = array())
    {
        return parent::rawGetFieldData('created_at', $default, $filter);
    }
}