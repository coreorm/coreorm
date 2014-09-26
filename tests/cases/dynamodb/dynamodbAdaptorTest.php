<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\MySQL;
use CoreORM\Adaptor\Dynamodb;
/**
 * test core
 */
class TestPdoDynamodb extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        Pdo::purgePdoAdaptor();
    }

    public function setUp()
    {
        parent::setUp();
        Pdo::purgePdoAdaptor();
    }

    public function testBaseAdaptor()
    {
        new Dynamodb(array(
            'profile' => 'coreorm-test',
            'region' => 'ap-southeast-2',
        ));

        new Dynamodb(array(
            'profile' => 'coreorm-test',
            'region' => 'ap-southeast-2',
        ));
        // and both should be using the same PDO...
        $pdos = Pdo::getPdoAdaptor();
//        dump(array_keys($pdos));
        $this->assertEquals(count($pdos), 1);
        // now add yet a new one with different config
        new Dynamodb(array(
            'profile' => 'coreorm-test2',
            'region' => 'ap-southeast-2',
        ));
        $pdos = Pdo::getPdoAdaptor();
        $this->assertEquals(count($pdos), 2);
//        dump(array_keys($pdos));
    }
}
?>
