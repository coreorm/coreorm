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
            'required' => '1',
            'field' => 'id',
            'field_key' => 'user_id',
            'field_map' => '`user`.`id`',
            'getter' => 'setId',
            'setter' => 'getId',
        ),
        'user_name' => array(
            'type' => 'string',
            'required' => '',
            'field' => 'name',
            'field_key' => 'user_name',
            'field_map' => '`user`.`name`',
            'getter' => 'setName',
            'setter' => 'getName',
        ),
        'user_address' => array(
            'type' => 'string',
            'required' => '',
            'field' => 'address',
            'field_key' => 'user_address',
            'field_map' => '`user`.`address`',
            'getter' => 'setAddress',
            'setter' => 'getAddress',
        ),
        'user_birthdate' => array(
            'type' => 'datetime',
            'required' => '',
            'field' => 'birthdate',
            'field_key' => 'user_birthdate',
            'field_map' => '`user`.`birthdate`',
            'getter' => 'setBirthdate',
            'setter' => 'getBirthdate',
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
     * @return int
     */
    public function getId($default = null)
    {
        return parent::rawGetFieldData('user_id', $default);
    }
    /**
     * retrieve Name
     * @param mixed $default
     * @return string
     */
    public function getName($default = null)
    {
        return parent::rawGetFieldData('user_name', $default);
    }
    /**
     * retrieve Address
     * @param mixed $default
     * @return string
     */
    public function getAddress($default = null)
    {
        return parent::rawGetFieldData('user_address', $default);
    }
    /**
     * retrieve Birthdate
     * @param string $format
     * @param mixed $default
     * @return datetime
     */
    public function getBirthdate($format = 'jS F, Y H:i', $default = null)
    {
        return parent::formatDateByName('user_birthdate', $format, $default);
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