<?php
/**
 * File model
 * @author ModelGenerator
 */
namespace Example\Model;
use CoreORM\Model;
class File extends Model
{
    CONST FIELD_ID = '`attachment`.`id`';
    CONST FIELD_USER_ID = '`attachment`.`user_id`';
    CONST FIELD_FILENAME = '`attachment`.`filename`';
    CONST FIELD_SIZE = '`attachment`.`size`';

    protected $table = 'attachment';
    protected $fields = array(
        'attachment_id' => array(
            'type' => 'int',
            'required' => '1',
            'field' => 'id',
            'field_key' => 'attachment_id',
            'field_map' => '`attachment`.`id`',
            'getter' => 'setId',
            'setter' => 'getId',
        ),
        'attachment_user_id' => array(
            'type' => 'int',
            'required' => '1',
            'field' => 'user_id',
            'field_key' => 'attachment_user_id',
            'field_map' => '`attachment`.`user_id`',
            'getter' => 'setUserId',
            'setter' => 'getUserId',
        ),
        'attachment_filename' => array(
            'type' => 'string',
            'required' => '',
            'field' => 'filename',
            'field_key' => 'attachment_filename',
            'field_map' => '`attachment`.`filename`',
            'getter' => 'setFilename',
            'setter' => 'getFilename',
        ),
        'attachment_size' => array(
            'type' => 'float',
            'required' => '',
            'field' => 'size',
            'field_key' => 'attachment_size',
            'field_map' => '`attachment`.`size`',
            'getter' => 'setSize',
            'setter' => 'getSize',
        ),
    );
    protected $key = array('attachment_id');
    protected $relations = array(
    );
    
    /**
     * set Id
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        return parent::rawSetFieldData('attachment_id', $value);
    }
    /**
     * set UserId
     * @param mixed $value
     * @return $this
     */
    public function setUserId($value)
    {
        return parent::rawSetFieldData('attachment_user_id', $value);
    }
    /**
     * set Filename
     * @param mixed $value
     * @return $this
     */
    public function setFilename($value)
    {
        return parent::rawSetFieldData('attachment_filename', $value);
    }
    /**
     * set Size
     * @param mixed $value
     * @return $this
     */
    public function setSize($value)
    {
        return parent::rawSetFieldData('attachment_size', $value);
    }
    
    /**
     * retrieve Id
     * @param mixed $default
     * @return int
     */
    public function getId($default = null)
    {
        return parent::rawGetFieldData('attachment_id', $default);
    }
    /**
     * retrieve UserId
     * @param mixed $default
     * @return int
     */
    public function getUserId($default = null)
    {
        return parent::rawGetFieldData('attachment_user_id', $default);
    }
    /**
     * retrieve Filename
     * @param mixed $default
     * @return string
     */
    public function getFilename($default = null)
    {
        return parent::rawGetFieldData('attachment_filename', $default);
    }
    /**
     * retrieve Size
     * @param mixed $default
     * @return float
     */
    public function getSize($default = null)
    {
        return parent::rawGetFieldData('attachment_size', $default);
    }
}