<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\Sqlite;
$dir = realpath(__DIR__ . '/../../') . '/support/tmp/';
is_writable($dir) or exit('[ERROR!] Please make sure ' . $dir . ' is created and writable' . PHP_EOL);
define('SQLITE_DB1', $dir . '/test.sqlite3');
define('SQLITE_DB2', $dir . '/test2.sqlite3');
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
            'dbname' => SQLITE_DB1
        ));

        new Sqlite(array(
            'dbname' => SQLITE_DB1
        ));
        // and both should be using the same PDO...
        $pdos = Pdo::getPdoAdaptor();
        $this->assertEquals(count($pdos), 1);
        // now add yet a new one with different config
        new Sqlite(array(
            'dbname' => SQLITE_DB2
        ));
        $pdos = Pdo::getPdoAdaptor();
        $this->assertEquals(count($pdos), 2);
    }
}
?>