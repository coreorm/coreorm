<?php
/**
 * Combination model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model;
class Combination extends Model
{
    CONST FIELD_ID_1 = '`combined_key_table`.`id_1`';
    CONST FIELD_ID_2 = '`combined_key_table`.`id_2`';
    CONST FIELD_NAME = '`combined_key_table`.`name`';
    CONST FIELD_USER_ID = '`combined_key_table`.`user_id`';

    protected $table = 'combined_key_table';
    protected $fields = array(
        'combined_key_table_id_1' => array(
            'type' => 'int',
            'required' => true,
            'field' => 'id_1',
            'field_key' => 'combined_key_table_id_1',
            'field_map' => '`combined_key_table`.`id_1`',
            'getter' => 'getId1',
            'setter' => 'setId1',
        ),
        'combined_key_table_id_2' => array(
            'type' => 'int',
            'required' => true,
            'field' => 'id_2',
            'field_key' => 'combined_key_table_id_2',
            'field_map' => '`combined_key_table`.`id_2`',
            'getter' => 'getId2',
            'setter' => 'setId2',
        ),
        'combined_key_table_name' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'name',
            'field_key' => 'combined_key_table_name',
            'field_map' => '`combined_key_table`.`name`',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'combined_key_table_user_id' => array(
            'type' => 'int',
            'required' => false,
            'field' => 'user_id',
            'field_key' => 'combined_key_table_user_id',
            'field_map' => '`combined_key_table`.`user_id`',
            'getter' => 'getUserId',
            'setter' => 'setUserId',
        ),
    );
    protected $key = array('combined_key_table_id_1', 'combined_key_table_id_2');
    protected $relations = array(
        'login' => array(
            'class' => 'Example\Model\Login',
            'type' => Model::RELATION_SINGLE,
            'join' => ' INNER JOIN `login` ON `combined_key_table`.`user_id` = `login`.`user_id` ',
            'condition' => '',
            'setter' => 'relationSetLogin',
            'getter' => 'relationGetLogin',
        ),
        'user' => array(
            'class' => 'Example\Model\User',
            'type' => Model::RELATION_SINGLE,
            'join' => ' INNER JOIN `user` ON `combined_key_table`.`user_id` = `user`.`id` ',
            'condition' => '',
            'setter' => 'relationSetUser',
            'getter' => 'relationGetUser',
        ),
    );
    
    /**
     * set Id1
     * @param mixed $value
     * @return $this
     */
    public function setId1($value)
    {
        return parent::rawSetFieldData('combined_key_table_id_1', $value);
    }
    /**
     * set Id2
     * @param mixed $value
     * @return $this
     */
    public function setId2($value)
    {
        return parent::rawSetFieldData('combined_key_table_id_2', $value);
    }
    /**
     * set Name
     * @param mixed $value
     * @return $this
     */
    public function setName($value)
    {
        return parent::rawSetFieldData('combined_key_table_name', $value);
    }
    /**
     * set UserId
     * @param mixed $value
     * @return $this
     */
    public function setUserId($value)
    {
        return parent::rawSetFieldData('combined_key_table_user_id', $value);
    }
    /**
     * set related Login model
     * @param Login $Login
     */
    public function relationSetLogin(Login $Login)
    {
        $this->data['_relation_Login'] = $Login;
    }
    /**
     * set related User model
     * @param User $User
     */
    public function relationSetUser(User $User)
    {
        $this->data['_relation_User'] = $User;
    }
    
    /**
     * retrieve Id1
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getId1($default = null, $filter = array())
    {
        return parent::rawGetFieldData('combined_key_table_id_1', $default, $filter);
    }
    /**
     * retrieve Id2
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getId2($default = null, $filter = array())
    {
        return parent::rawGetFieldData('combined_key_table_id_2', $default, $filter);
    }
    /**
     * retrieve Name
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getName($default = null, $filter = array())
    {
        return parent::rawGetFieldData('combined_key_table_name', $default, $filter);
    }
    /**
     * retrieve UserId
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getUserId($default = null, $filter = array())
    {
        return parent::rawGetFieldData('combined_key_table_user_id', $default, $filter);
    }
    /**
     * get related Login model
     * @return Login
     */
    public function relationGetLogin()
    {
        if (!empty($this->data['_relation_Login'])) {
            return $this->data['_relation_Login'];
        }
        return new Login();
    }
    /**
     * get related User model
     * @return User
     */
    public function relationGetUser()
    {
        if (!empty($this->data['_relation_User'])) {
            return $this->data['_relation_User'];
        }
        return new User();
    }
}