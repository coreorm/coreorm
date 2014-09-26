<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../support/Example/Model.Dynamo/Mock.php';
use CoreORM\Adaptor\Dynamodb, CoreORM\Utility\Debug, \Example\Model\Mock,
    \Aws\DynamoDb\Enum\ComparisonOperator, Aws\DynamoDb\Enum\Type;
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
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->adaptor);
    }

    public function testCreateTable()
    {
        $this->adaptor->createTableIfNotExists($this->schema, true);
        $this->assertTrue($this->adaptor->tableExists(self::table));
    }


    public function testDescribe()
    {
        $tableInfo = $this->adaptor->describe(self::table);
        $this->assertNotEmpty($tableInfo);

    }

    public function testInsert()
    {
        $item = new Mock();
        $item->rawSetFieldData('notification_id', 'notification id here')
             ->rawSetFieldData('sns_message_id', 'test message id')
             ->rawSetFieldData('job_assignment_id', 12345678)
             ->rawSetJsonData('data', 'test', 123);
        $result = $this->adaptor->putItem($item);
        $this->assertNotEmpty($result->get('ConsumedCapacity'));

        $item->rawSetFieldData('notification_id', 'notification id 2')
            ->rawSetFieldData('sns_message_id', 'test xxxx id')
            ->rawSetFieldData('job_assignment_id', 1111)
            ->rawSetJsonData('data', 'test', 22)
            ->rawSetJsonData('data', 'something', 'asdfas');
        $result = $this->adaptor->putItem($item);
        $this->assertNotEmpty($result->get('ConsumedCapacity'));
    }

    public function testQuery()
    {
        $item = new Mock();
        $item->querySetCondition('notification_id', 'EQ', 'S', 'notification id here');
        $result = $this->adaptor->queryItem($item);
        $this->assertEquals(count($result->get('Items')), 1);
    }

    public function testScan()
    {
        $item = new Mock();
        $item->querySetCondition('notification_id', ComparisonOperator::CONTAINS, Type::STRING, 'notification');
        $result = $this->adaptor->scanItems($item);
        $this->assertEquals(count($result->get('Items')), 2);
    }

    public function testDelete()
    {
        $item = new Mock();
        $item->rawSetFieldData('notification_id', 'notification id here');
        $this->adaptor->deleteItem($item);
        $item->rawSetFieldData('notification_id', 'notification id 2');
        $this->adaptor->deleteItem($item);
        $item = new Mock();
        $item->querySetCondition('notification_id', ComparisonOperator::CONTAINS, Type::STRING, 'notification');
        $result = $this->adaptor->scanItems($item);
        $this->assertEquals(count($result->get('Items')), 0);

    }

    public function testDropTable()
    {
        $this->adaptor->dropTable(self::table);
        Debug::output();

    }

}
