<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Dao\Base;
use CoreORM\Adaptor\Pdo;
use CoreORM\Utility\Debug;
/**
 * test core
 */
class TestDaoAdaptor extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        // build db confs in memory
        setDbConfig('default', 'db1');
        setDbConfig('database', array(
            'db1' => array(
                'adaptor' => Pdo::ADAPTOR_MYSQL,
                'host' => '127.0.0.1',
                'user' => 'core',
                'pass' => 'test',
                'dbname' => 'coreorm',
                'slave' => 'db1_slave',
                'cache' => true,
            ),
            'db1_slave' => array(
                'adaptor' => Pdo::ADAPTOR_MYSQL,
                'host' => '127.0.0.1',
                'user' => 'core_slave',
                'pass' => 'test',
                'dbname' => 'coreorm',
                'cache' => true,
            )
        ));
        // add new table and insert a few rows...
        $sql = 'CREATE TABLE `test` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `test` varchar(200) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $dao = new Base();
        // add table...
        $dao->query($sql);
        // insert data
        $data = array(
            array('test' => 'test 1'),
            array('test' => 'test 2'),
            array('test' => 'test 3'),
        );
        foreach ($data as $row) {
            $dao->insert($row, 'test');
        }
    }


    public function tearDown()
    {
        parent::tearDown();
        $dao = new Base();
        // add table...
        $sql = 'drop table `test`;';
        $dao->query($sql);
        // clear all adaptors
        CoreORM\Adaptor\Pdo::purgePdoAdaptor();
        // at the end show queries run
        Debug::output();

    }


    public function testAdaptor()
    {
        $dao = new Base();
        $dao2 = new Base();
        $this->assertEquals($dao->adaptor(), $dao2->adaptor());
        // next, do a query
        $sql = 'select * from test;';
        $results = $dao->fetchAll($sql);
        $this->assertEquals(count($results), 3);

    }


    public function testSlave()
    {
        $dao = new Base();
        $dao2 = new Base();
        $this->assertEquals($dao->adaptor(), $dao2->adaptor());
        // next, do a query
        $sql = 'select * from test;';
        $results = $dao->fetchAll($sql, null, true);
        $this->assertEquals(count($results), 3);

    }
}