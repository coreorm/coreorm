<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Dynamodb, CoreORM\Utility\Debug;
/**
 * test core
 */
class TestCrudDynamo extends PHPUnit_Framework_TestCase
{
    protected $opts = array(
        'profile' => 'bruceli',
        'region' => 'ap-southeast-2',
    );

    const table = 'hip-pushwatcher-notification-deliverability2014-09-26Test';

    /**
     * @var Dynamodb
     */
    protected $adaptor;

    protected $schema = array(
        'TableName' => self::table,
        'AttributeDefinitions' => [
            array('AttributeName' => 'notification_id',     'AttributeType' => 'S')
        ],
        'KeySchema' => [
            array('AttributeName' => 'notification_id',     'KeyType' => 'HASH'),
        ],
        'ProvisionedThroughput' => array(
            'ReadCapacityUnits'  => 10,
            'WriteCapacityUnits' => 10
        )
    );
    /*
     then we push more to it later...

    array('AttributeName' => 'sns_message_id',      'AttributeType' => 'S'),
    array('AttributeName' => 'job_assignment_id',   'AttributeType' => 'N'),
    array('AttributeName' => 'token',               'AttributeType' => 'S'),
    array('AttributeName' => 'os',                  'AttributeType' => 'S'),
    array('AttributeName' => 'data',                'AttributeType' => 'S'),
    array('AttributeName' => 'created_at',          'AttributeType' => 'N'),
    array('AttributeName' => 'sent_to_sns_at',      'AttributeType' => 'N'),
    array('AttributeName' => 'arrive_at_device_at', 'AttributeType' => 'N'),
     */

    const DATA = 'some test data to be inserted';

    public function setUp()
    {
        parent::setUp();
        $this->adaptor = new Dynamodb($this->opts);
        $this->adaptor->createTableIfNotExists($this->schema, true);
    }

    public function tearDown()
    {
        parent::tearDown();
//        $this->adaptor->dropTable(self::table);
        unset($this->adaptor);
    }

    public function testDescribe()
    {
        $tableInfo = $this->adaptor->describe(self::table);
        $this->assertNotEmpty($tableInfo);

    }

    public function testInsert()
    {
        $item = new TestSampleItem();
        $item->rawSetFieldData('notification_id', 'notification id here')
             ->rawSetFieldData('sns_message_id', 'message id here')
             ->rawSetFieldData('job_assignment_id', 12345678);
        $this->adaptor->putItem($item);


    }

    public function testQuery()
    {
        $item = new TestSampleItem();
        $item->querySetCondition('notification_id', 'EQ', 'S', 'notification id here');
        $result = $this->adaptor->queryItem($item);
        dump($result->get('Item'));
        Debug::output(0);
    }

    public function testScan()
    {

    }

    public function testDelete()
    {
        $item = new TestSampleItem();
        $item->querySetDeleteCondition('notification_id', \Aws\DynamoDb\Enum\Type::S, 'notification id here');
        $result = $this->adaptor->deleteItem($item);
        dump($result);

    }

}

class TestSampleItem extends \CoreORM\Model\Dynamodb
{
    protected $table = 'hip-pushwatcher-notification-deliverability2014-09-26Test';
    protected $fields = array(
        'notification_id' => array(
            'type' => 'string',
            'field_map' => 'notification_id',
            'field' => 'notification_id'
        ),
        'sns_message_id' => array(
            'type' => 'string',
            'field_map' => 'sns_message_id',
            'field' => 'sns_message_id'
        ),
        'job_assignment_id' => array(
            'type' => 'int',
            'field_map' => 'job_assignment_id',
            'field' => 'job_assignment_id'
        ),
    );
}
