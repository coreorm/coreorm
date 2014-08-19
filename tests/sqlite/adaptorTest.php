<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\Sqlite;
define('SQLITE_DB1', __DIR__ . '/../tmp/test.sqlite3');
define('SQLITE_DB2', __DIR__ . '/../tmp/test2.sqlite3');
/**
 * test core
 */
class TestPdo extends PHPUnit_Framework_TestCase
{

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