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
    protected $table = 'hip-pushwatcher-notification-deliverability2014-09-26Test';
    protected $fields = array(
        'notification_id' => array(
            'type' => 'string',
            'field' => 'notification_id',
            'required' => '1',
            'field_key' => 'notification_id',
            'field_map' => 'notification_id',
        ),
        'sns_message_id' => array(
            'type' => 'string',
            'field_map' => 'sns_message_id',
            'field' => 'sns_message_id',
            'field_key' => 'sns_message_id',
        ),
        'job_assignment_id' => array(
            'type' => 'int',
            'field_map' => 'job_assignment_id',
            'field' => 'job_assignment_id',
            'field_key' => 'job_assignment_id',
        ),
        'data' => array(
            'type' => 'string',
            'field_map' => 'data',
            'field' => 'data',
            'field_key' => 'data',
        ),
    );
    protected $key = array('notification_id');

}
