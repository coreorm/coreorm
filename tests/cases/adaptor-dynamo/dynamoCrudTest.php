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
        'profile' => 'coreorm-test',
        'region' => 'ap-southeast-2',
    );

    const table = 'test-user-data';

    /**
     * @var Dynamodb
     */
    protected $adaptor;

    protected $schema = array(
        'TableName' => self::table,
        'AttributeDefinitions' => [
            array('AttributeName' => 'id',     'AttributeType' => 'S')
        ],
        'KeySchema' => [
            array('AttributeName' => 'id',     'KeyType' => 'HASH'),
        ],
        'ProvisionedThroughput' => array(
            'ReadCapacityUnits'  => 10,
            'WriteCapacityUnits' => 10
        )
    );

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
        $item->rawSetFieldData('id', 'id12345')
             ->rawSetFieldData('foo', 'some test')
             ->rawSetFieldData('bar', 12345678)
             ->rawSetJsonData('data', 'foo', 123);
        $result = $this->adaptor->putItem($item);
        $this->assertNotEmpty($result->get('ConsumedCapacity'));


        $item->rawSetFieldData('id', 'id12345-2')
            ->rawSetFieldData('foo', 'some test')
            ->rawSetFieldData('bar', 12345678)
            ->rawSetJsonData('data', 'foo', 22)
            ->rawSetJsonData('data', 'something', 'asdfas');
        $result = $this->adaptor->putItem($item);
        $this->assertNotEmpty($result->get('ConsumedCapacity'));
    }

    public function testUpdate()
    {
        $item = new Mock();
        $item->rawSetFieldData('id', 'id12345')
             ->rawSetFieldData('time', 'a totally new attribute!');
        $this->adaptor->updateItem($item);
        // then retrieve
        $item->querySetCondition('id', 'EQ', 'S', 'id12345');
        $result = $this->adaptor->queryItem($item);
        $data = current($result->get('Items'));
        $this->assertEquals(current($data['time']), $item->rawGetFieldData('time'));

    }

    public function testQuery()
    {
        $item = new Mock();
        $item->querySetCondition('id', 'EQ', 'S', 'id12345');
        $result = $this->adaptor->queryItem($item);
        $this->assertEquals(count($result->get('Items')), 1);
    }

    public function testScan()
    {
        $item = new Mock();
        $item->querySetCondition('id', ComparisonOperator::CONTAINS, Type::STRING, 'id12345');
        $result = $this->adaptor->scanItems($item);
        $this->assertEquals(count($result->get('Items')), 2);
    }

    public function testDelete()
    {
        $item = new Mock();
        $item->rawSetFieldData('id', 'id12345');
        $this->adaptor->deleteItem($item);
        $item->rawSetFieldData('id', 'id12345-2');
        $this->adaptor->deleteItem($item);
        $item = new Mock();
        $item->querySetCondition('id', ComparisonOperator::CONTAINS, Type::STRING, 'id1234');
        $result = $this->adaptor->scanItems($item);
        $this->assertEquals(count($result->get('Items')), 0);

    }

    public function testDropTable()
    {
        $this->adaptor->dropTable(self::table, true);
        Debug::output();

    }

}
