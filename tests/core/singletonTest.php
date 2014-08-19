<?php
require_once __DIR__ . '/../header.php';
/**
 * test core
 */
class TestSingleton extends PHPUnit_Framework_TestCase
{
    public function testSingletone()
    {
        // get 1 single ton then get it out
        $obj = \CoreORM\Core::singleton('MockObject');
        $this->assertInstanceOf('MockObject', $obj, 'object should be mockobject');
        // next, get another
        $obj2 = \CoreORM\Core::singleton('MockObject');
        $this->assertEquals($obj, $obj2);
    }

}

class MockObject
{
    public function __construct()
    {
        echo 'do something';

    }

}