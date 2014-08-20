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
            'getter' => 'setUserId',
            'setter' => 'getUserId',
        ),
        'login_username' => array(
            'type' => 'string',
            'required' => '1',
            'field' => 'username',
            'field_key' => 'login_username',
            'field_map' => '`login`.`username`',
            'getter' => 'setUsername',
            'setter' => 'getUsername',
        ),
        'login_password' => array(
            'type' => 'string',
            'required' => '1',
            'field' => 'password',
            'field_key' => 'login_password',
            'field_map' => '`login`.`password`',
            'getter' => 'setPassword',
            'setter' => 'getPassword',
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
     * @return int
     */
    public function getUserId($default = null)
    {
        return parent::rawGetFieldData('login_user_id', $default);
    }
    /**
     * retrieve Username
     * @param mixed $default
     * @return string
     */
    public function getUsername($default = null)
    {
        return parent::rawGetFieldData('login_username', $default);
    }
    /**
     * retrieve Password
     * @param mixed $default
     * @return string
     */
    public function getPassword($default = null)
    {
        return parent::rawGetFieldData('login_password', $default);
    }
}