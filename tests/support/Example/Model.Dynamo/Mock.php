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

}
