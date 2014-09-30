<?php
require_once __DIR__ . '/../header.php';
use Example\Model\User, \CoreORM\Utility\Debug;
$modelDir = realpath(__DIR__ . '/../../support/Example/Model') . '/';
require_once $modelDir . 'User.php';
/**
 * model test
 *
 */
class TestModel extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $nameRaw = "it\'s a test with a <Tag>";
        $nameHtml = ucfirst(htmlentities($nameRaw));
        $model = new User();
        $model->setId(1)
              ->setName($nameRaw);
        // now let's try getting the array of data out
        $arrayRaw = $model->toArray();
        $arrayHtml = $model->toArray(false, array(User::FIELD_NAME => array('htmlentities', 'ucfirst')));
        // 1st of all, they should be different
        $this->assertNotEquals($arrayRaw, $arrayHtml);
        $this->assertEquals($arrayHtml['name'], $nameHtml);

    }

    public function testToJson()
    {
        $nameRaw = 'Test with <a>tag</a>';
        $nameHtml = htmlentities($nameRaw);
        $model = new User();
        $model->setId(1)
            ->setName($nameRaw);
        // now let's try getting the array of data out
        $jsonRaw = $model->toJson();
        $jsonHtml = $model->toJson(false, array(User::FIELD_NAME => 'htmlentities'));
        // 1st of all, they should be different
        $this->assertNotEquals($jsonRaw, $jsonHtml);
        // reverse
        $obj = json_decode($jsonHtml);
        $this->assertEquals($obj->name, $nameHtml);
        Debug::setUserData('equal:', $obj->name . ' = ' . $nameHtml);
    }

    public function testDatetime()
    {
        $date = date('Y-m-d H:i:s');
        $user = new User(array(
            'user_id' => 12,
            'user_birthdate' => $date
        ));
        $newDate = $user->getBirthdate('F d Y', null, array('strtolower'));
        $this->assertEquals($newDate, strtolower(date('F d Y', strtotime($date))));
        Debug::setUserData('equal:', $newDate . ' = ' . strtolower(date('F d Y', strtotime($date))));
    }


    public function testInit()
    {
        $user = new InitMock();
        $this->assertEquals($user->table(), 'new user table');
    }

    public function testDebug()
    {
        Debug::output();
    }

}
// mock for init test
class InitMock extends User
{
    public function init()
    {
        parent::init();
        $this->table('new user table');
    }
}
