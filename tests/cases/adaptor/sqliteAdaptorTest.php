<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\Sqlite;
/**
 * test core
 */
class TestSQLite extends PHPUnit_Framework_TestCase
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
        new Sqlite(array(
            'dbname' => ':memory:'
        ));

        new Sqlite(array(
            'dbname' => ':memory:'
        ));
        // and both should be using the same PDO...
        $pdos = Pdo::getPdoAdaptor();
        dump($pdos);
        $this->assertEquals(count($pdos), 1);
        // with in-memory db there's no way to add a new one, so let's stop here :)
        // if mysql passes, this will pass
    }
}
?>