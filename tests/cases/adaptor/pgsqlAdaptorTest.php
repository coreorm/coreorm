<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\PGSQL;
/**
 * test core
 */
class TestPdoPGSQL extends PHPUnit_Framework_TestCase
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
        new PGSQL(array(
            'host' => '127.0.0.1',
            'user' => 'core',
            'pass' => 'test',
            'port' => '5432',
            'dbname' => 'coreorm'
        ));

        new PGSQL(array(
            'host' => '127.0.0.1',
            'user' => 'core',
            'pass' => 'test',
            'port' => '5432',
            'dbname' => 'coreorm'
        ));
        // and both should be using the same PDO...
        $pdos = Pdo::getPdoAdaptor();
        $this->assertEquals(count($pdos), 1);
        // now add yet a new one with different config
        new PGSQL(array(
            'host' => '127.0.0.1',
            'user' => 'core',
            'pass' => 'test',
            'port' => '5432',
            'dbname' => 'postgres'
        ));
        $pdos = Pdo::getPdoAdaptor();
        $this->assertEquals(count($pdos), 2);
    }
}
?>
