<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\Sqlite;
$dir = realpath(__DIR__ . '/../../') . '/support/tmp/';
is_writable($dir) or exit('[ERROR!] Please make sure ' . $dir . ' is created and writable' . PHP_EOL);
define('SQLITE_DB', $dir . 'test.sqlite3');
/**
 * test core
 */
class TestCrudSqlite extends PHPUnit_Framework_TestCase
{
    protected $opts = array(
        'dbname' => SQLITE_DB,
    );

    /**
     * @var Sqlite
     */
    protected $adaptor;

    const DATA = 'some test data to be inserted';

    public function setUp()
    {
        parent::setUp();
        // run a quick table here
        $sql = 'CREATE TABLE `test` (
        `id` INTEGER PRIMARY KEY,
        `test` TEXT
        )';
        $this->adaptor = new Sqlite($this->opts);
        $this->adaptor->query($sql);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->adaptor->query('DROP TABLE `test`;');
        unset($this->adaptor);
    }

    public function testDescribe()
    {
        $tableInfo = $this->adaptor->describe('test');
        $this->assertNotEmpty($tableInfo);
    }

    public function testInsert()
    {
        $data = array('test' => self::DATA);
        $id = (int) $this->adaptor->insert($data, 'test');
        $this->assertTrue($id > 0);
        return $id;
    }

    public function testFetchOne()
    {
        $id = $this->testInsert();
        $data = $this->adaptor->fetchOne('SELECT test FROM test WHERE id = ?', array($id));
        $this->assertEquals($data, self::DATA);
    }

    public function testFetchAll()
    {
        $this->testInsert();
        $this->testInsert();
        $this->testInsert();
        $data = $this->adaptor->fetchAll('SELECT test FROM test');
        $this->assertEquals(count($data), 3);
        dump($data);
    }

    public function testDelete()
    {
        $id = $this->testInsert();
        $this->adaptor->delete('test', 'id=?', array($id));
        // read - it should NOT be there any more
        $data = $this->adaptor->fetchOne('SELECT `test` FROM `test` WHERE id = ?', array($id));
        $this->assertEmpty($data);
    }

}