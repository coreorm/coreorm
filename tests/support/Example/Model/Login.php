<?php
/**
 * Login model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model;
class Login extends Model
{
    CONST FIELD_USER_ID = '`login`.`user_id`';
    CONST FIELD_USERNAME = '`login`.`username`';
    CONST FIELD_PASSWORD = '`login`.`password`';

    protected $table = 'login';
    protected $fields = array(
        'login_user_id' => array(
            'type' => 'int',
            'required' => '1',
            'field' => 'user_id',
            'field_key' => 'login_user_id',
            'field_map' => '`login`.`user_id`',
            'getter' => 'getUserId',
            'setter' => 'setUserId',
        ),
        'login_username' => array(
            'type' => 'string',
            'required' => '1',
            'field' => 'username',
            'field_key' => 'login_username',
            'field_map' => '`login`.`username`',
            'getter' => 'getUsername',
            'setter' => 'setUsername',
        ),
        'login_password' => array(
            'type' => 'string',
            'required' => '1',
            'field' => 'password',
            'field_key' => 'login_password',
            'field_map' => '`login`.`password`',
            'getter' => 'getPassword',
            'setter' => 'setPassword',
        ),
    );
    protected $key = array('login_user_id');
    protected $relations = array(
    );
    
    /**
     * set UserId
     * @param mixed $value
     * @return $this
     */
    public function setUserId($value)
    {
        return parent::rawSetFieldData('login_user_id', $value);
    }
    /**
     * set Username
     * @param mixed $value
     * @return $this
     */
    public function setUsername($value)
    {
        return parent::rawSetFieldData('login_username', $value);
    }
    /**
     * set Password
     * @param mixed $value
     * @return $this
     */
    public function setPassword($value)
    {
        return parent::rawSetFieldData('login_password', $value);
    }
    
    /**
     * retrieve UserId
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getUserId($default = null, $filter = array())
    {
        return parent::rawGetFieldData('login_user_id', $default, $filter);
    }
    /**
     * retrieve Username
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getUsername($default = null, $filter = array())
    {
        return parent::rawGetFieldData('login_username', $default, $filter);
    }
    /**
     * retrieve Password
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getPassword($default = null, $filter = array())
    {
        return parent::rawGetFieldData('login_password', $default, $filter);
    }
}