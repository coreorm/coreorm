<?php
/**
 * dao dynamodb
 *
 */
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../support/Example/Model.Dynamo/Mock.php';
use \CoreORM\Dao\Orm, \CoreORM\Utility\Config, CoreORM\Utility\Debug, \Example\Model\Mock,
    \Aws\DynamoDb\Enum\ComparisonOperator, Aws\DynamoDb\Enum\Type;

/**
 * Class TestCrudDynamoDao
 */
class TestCrudDynamoDao extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orm
     */
    protected $dao;

    const table = 'test-user-data';

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

    protected $conf = array(
        'main' => array(
            'profile' => 'coreorm-test',
            'region' => 'ap-southeast-2',
            'adaptor' => CoreORM\Adaptor\Pdo::ADAPTOR_DYNAMODB
        ),
    );


    public function setUp()
    {
        parent::setUp();
        Config::set('database', $this->conf);
        $this->dao = new Orm('main');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->dao);

    }

    public function testCreateTable()
    {
        $this->dao->createTableIfNotExists($this->schema, true);
        $this->assertTrue($this->dao->tableExists(self::table));
    }

    public function testCRUD()
    {
        $id   = 'new-test-id here';
        $mock = new Mock();
        $mock->setId($id)
             ->setFoo('hello this is foo')
             ->setBar(12312321)
             ->setData('new data piece here');
        $result = $this->dao->writeModel($mock);
        $this->assertNotEmpty($result->get('ConsumedCapacity'));
        $data = $mock->toArray();
        // then we do read
        // query
        $mock = new Mock();
        $mock->setId($id);
        $result = $this->dao->readModel($mock, Orm::FETCH_MODEL_QUERY);
        Debug::setUserData('read model data', $result->toJson());
        $this->assertEquals($data, $mock->toArray());
        // scan
        $mock = new Mock();
        $mock->setId($id);
        $result = $this->dao->readModel($mock, Orm::FETCH_MODEL_SCAN);
        Debug::setUserData('read model data', $result->toJson());
        $this->assertEquals($data, $mock->toArray());

        // test multiple
        // insert a few.
        for ($i = 0; $i <= 3; $i ++) {
            $mock = new Mock();
            $mock->setId($id . '-' . $i)
                 ->setData('data is ' . $i);
            $this->dao->writeModel($mock);
        }
        // retrieve all
        $mock = new Mock();
        $mock->querySetCondition('id', ComparisonOperator::CONTAINS, Type::STRING, $id);
        $results = $this->dao->readModels($mock, array(
            'fetchMode' => Orm::FETCH_MODEL_SCAN
        ));
        $this->assertTrue(count($results) == 5);
    }

    public function testDropTable()
    {
//        $this->dao->dropTable(self::table);
        Debug::output();

    }

}
