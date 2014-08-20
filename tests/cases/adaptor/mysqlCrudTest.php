<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Adaptor\MySQL;
/**
 * test core
 */
class TestCrudMySQL extends PHPUnit_Framework_TestCase
{
    protected $opts = array(
        'host' => '127.0.0.1',
        'user' => 'core',
        'pass' => 'test',
        'dbname' => 'coreorm'
    );

    /**
     * @var MySQL
     */
    protected $adaptor;

    const DATA = 'some test data to be inserted';

    public function setUp()
    {
        parent::setUp();
        // run a quick table here
        $sql = 'CREATE TABLE `test` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `test` varchar(200) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $this->adaptor = new MySQL($this->opts);
        $this->adaptor->query($sql);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->adaptor->query('drop table test;');
        unset($this->adaptor);
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
        $data = $this->adaptor->fetchOne('SELECT test FROM test WHERE id = ?', array($id));
        $this->assertEmpty($data);
    }

}