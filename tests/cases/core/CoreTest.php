<?php
/**
 * test of the core
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Core;
class TestCore extends PHPUnit_Framework_TestCase
{
    // test core function
    public function testSingleton()
    {
        // singleton
        // get 1 single ton then get it out
        ob_start();
        $obj = \CoreORM\Core::singleton('MockObject');
        $string = ob_get_clean();
        // we will see output from the constructor, so
        $this->assertEquals($string, 'do something');
        $this->assertInstanceOf('MockObject', $obj, 'object should be mockobject');
        // next, get another
        ob_start();
        $obj2 = \CoreORM\Core::singleton('MockObject');
        $this->assertEquals($obj, $obj2);
        $string = ob_get_clean();
        // we will NOT see output from the constructor
        $this->assertEmpty($string);

    }

    public function testDebug()
    {
        // 1st. on
        CoreDebug(true);
        ob_start();
        _dump('test');
        $data = ob_get_clean();
        $this->assertNotEmpty($data);

        // 2nd off
        CoreDebug(false);
        ob_start();
        _dump('test');
        $data = ob_get_clean();
        $this->assertEmpty($data);

    }

    public function testStorage()
    {
        $k = 'test';
        $v = 'value for test';
        Core::store($k, $v);
        $this->assertEquals($v, Core::retrieve($k));

    }

}

class MockObject
{
    public function __construct()
    {
        echo 'do something';
    }

}