<?php
/**
 * User model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model;
class User extends Model
{
    CONST FIELD_ID = '`user`.`id`';
    CONST FIELD_NAME = '`user`.`name`';
    CONST FIELD_ADDRESS = '`user`.`address`';
    CONST FIELD_BIRTHDATE = '`user`.`birthdate`';

    protected $table = 'user';
    protected $fields = array(
        'user_id' => array(
            'type' => 'int',
            'required' => true,
            'field' => 'id',
            'field_key' => 'user_id',
            'field_map' => '`user`.`id`',
            'getter' => 'getId',
            'setter' => 'setId',
        ),
        'user_name' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'name',
            'field_key' => 'user_name',
            'field_map' => '`user`.`name`',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'user_address' => array(
            'type' => 'string',
            'required' => false,
            'field' => 'address',
            'field_key' => 'user_address',
            'field_map' => '`user`.`address`',
            'getter' => 'getAddress',
            'setter' => 'setAddress',
        ),
        'user_birthdate' => array(
            'type' => 'datetime',
            'required' => false,
            'field' => 'birthdate',
            'field_key' => 'user_birthdate',
            'field_map' => '`user`.`birthdate`',
            'getter' => 'getBirthdate',
            'setter' => 'setBirthdate',
        ),
    );
    protected $key = array('user_id');
    protected $relations = array(
        'login' => array(
            'class' => 'Example\Model\Login',
            'type' => Model::RELATION_SINGLE,
            'join' => ' INNER JOIN `login` ON `user`.`id` = `login`.`user_id` ',
            'condition' => '',
            'setter' => 'relationSetLogin',
            'getter' => 'relationGetLogin',
        ),
        'attachment' => array(
            'class' => 'Example\Model\File',
            'type' => Model::RELATION_MULTI,
            'join' => ' LEFT JOIN `attachment` ON `user`.`id` = `attachment`.`user_id` ',
            'condition' => '',
            'setter_multi' => 'relationSetFileList',
            'setter' => 'relationAddFile',
            'getter_multi' => 'relationGetFileList',
            'getter' => 'relationGetFileById',
        ),
    );
    
    /**
     * set Id
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        return parent::rawSetFieldData('user_id', $value);
    }
    /**
     * set Name
     * @param mixed $value
     * @return $this
     */
    public function setName($value)
    {
        return parent::rawSetFieldData('user_name', $value);
    }
    /**
     * set Address
     * @param mixed $value
     * @return $this
     */
    public function setAddress($value)
    {
        return parent::rawSetFieldData('user_address', $value);
    }
    /**
     * set Birthdate
     * @param mixed $value
     * @return $this
     */
    public function setBirthdate($value)
    {
        return parent::rawSetFieldData('user_birthdate', $value);
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
     * set related File model
     * @param File $File
     */
    public function relationAddFile(File $File)
    {
        return $this->addOneModel($File, '_relation_File');
    }
    /**
     * set related File list
     * @param $list
     * @return $this
     */
    public function relationSetFileList($list)
    {
        $this->data['_relation_File'] = $list;
        return $this;
    }
    
    /**
     * retrieve Id
     * @param mixed $default
     * @param array $filter filter call back function
     * @return int
     */
    public function getId($default = null, $filter = array())
    {
        return parent::rawGetFieldData('user_id', $default, $filter);
    }
    /**
     * retrieve Name
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getName($default = null, $filter = array())
    {
        return parent::rawGetFieldData('user_name', $default, $filter);
    }
    /**
     * retrieve Address
     * @param mixed $default
     * @param array $filter filter call back function
     * @return string
     */
    public function getAddress($default = null, $filter = array())
    {
        return parent::rawGetFieldData('user_address', $default, $filter);
    }
    /**
     * retrieve Birthdate
     * @param string $format
     * @param mixed $default
     * @param array $filter filter call back function
     * @return datetime
     */
    public function getBirthdate($format = 'jS F, Y H:i', $default = null, $filter = array())
    {
        return parent::formatDateByName('user_birthdate', $format, $default, $filter);
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
     * get related File model list
     * @return File
     */
    public function relationGetFileList()
    {
        if (!empty($this->data['_relation_File'])) {
            return (array) $this->data['_relation_File'];
        }
        return array();
    }
    /**
     * get related File model by id
     * @param $id
     * @return File
     */
    public function relationGetFileById($id)
    {
        return $this->getOneModel('_relation_File', $id, 'File');
    }
}