<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Model;
use Example\Model\User;
use \Example\Model\File;
use \CoreORM\Utility\Debug;
use Example\Model\Combination;
/**
 * test core
 */
class TestModel extends PHPUnit_Framework_TestCase
{
    /**
     * @var CoreORM\Dao\Orm
     */
    protected $dao;

    public function setUp()
    {
        setDbConfig('database', array(
            'orm_test' => array(
                "dbname" => "model_test",
                "user" => "model",
                "adaptor" => "MySQL",
                "pass" => "test",
                "host" => "127.0.0.1"
            )
        ));
        setDbConfig('default', 'orm_test');
        $this->dao = new \CoreORM\Dao\Orm();
    }

    public function testCRUD()
    {
        // we do the full crud test here...
        // 1st. read
        $user = new User();
        $user->setId(1);
        Debug::bench('readModel', array($user), $this->dao);
        $this->assertNotEmpty($user->getName());
        // 2nd. read all
        // 2.1 read unlimited (also bench it)
        $user = new User();
        $user->shouldJoinAll();
        $users = Debug::bench('readModels', array($user), $this->dao);
        foreach ($users as $u) {
            $this->assertInstanceOf('\Example\Model\User', $u);
            // next, login must be valid (even if empty)
            if ($u instanceof User) {
                $this->assertInstanceOf('\Example\Model\Login', $u->relationGetLogin());
                foreach ($u->relationGetFileList() as $file) {
                    $this->assertInstanceOf('\Example\Model\File', $file);
                }
            }
            // show it off!
            dump($u->toJson(true));
        }
        // 2.2 with limit
        $user = new User();
        $user->shouldJoinAll();
        $users = Debug::bench('readModels', array($user, null, null, null, 1), $this->dao);
        foreach ($users as $u) {
            $this->assertInstanceOf('\Example\Model\User', $u);
            // next, login must be valid (even if empty)
            if ($u instanceof User) {
                $this->assertInstanceOf('\Example\Model\Login', $u->relationGetLogin());
                foreach ($u->relationGetFileList() as $file) {
                    $this->assertInstanceOf('\Example\Model\File', $file);
                }
            }
            // show it off!
            dump($u->toJson(true));
        }
        $this->assertEquals(1, count($users));

        // 3. insert a single new one (and remember to remove it)
        $file = new File();
        $file->setUserId(2)
             ->setSize(123.12)
             ->setFilename('test.file');
        $this->dao->writeModel($file);
        $this->assertNotNull($file->primaryKey());
        // 4. read the file then delete
        $file = new File();
        $file->setUserId(2)
            ->setFilename('test.file');
        $this->dao->readModel($file);
        $this->assertNotNull($file->primaryKey(true));
        // next, delete
        $this->dao->deleteModel($file);
        $file = new File();
        $file->setUserId(2)
            ->setFilename('test.file');
        $this->dao->readModel($file);
        $this->assertEmpty($file->primaryKey(true));
        // partial model test
        $model = new User();
        $model->setId(1)
              ->shouldJoin(new File())
              ->shouldJoin(new \Example\Model\Login())
              ->partialSelect(array(
                    User::FIELD_NAME, User::FIELD_ADDRESS
                ));
        $this->dao->readModel($model);
        $this->assertEmpty($model->getBirthdate(false));
        // and this one should contain multiple files and 1 login item
        $this->assertInstanceOf('Example\Model\Login', $model->relationGetLogin());
        $this->assertTrue(count($model->relationGetFileList()) > 1);
        // now, test update...
        $m = $model->cloneMutable(); // and we don't want to update original
        $NewName = 'Name New' . time();
        if ($m instanceof User) {
            $m->setName($NewName);
            $this->dao->writeModel($m);
        }
        // let's retrieve and figure out
        $newModel = new User();
        $newModel->setId(1);
        $this->dao->readModel($newModel);
        $this->assertEquals($NewName, $newModel->getName());

        // combination key test
        $c = new Combination();
        $c->setId1(1)->setId2(2)->setUserId(1)->setName($NewName);
        $this->dao->writeModel($c);
        // now select out c...
        $c = new Combination();
        $c->setId1(1)->setId2(2)->setUserId(1)->shouldJoinAll();
        $this->dao->readModel($c);
        $this->assertEquals($NewName, $c->getName());
        // next, should contain 2 object
        $this->assertEquals($c->getUserId(), $c->relationGetUser()->getId());
        $this->assertEquals($c->getUserId(), $c->relationGetLogin()->getUserId());
        // then delete it...
        $this->dao->deleteModel($c);
        dump($c->toJson(true));

        // output benchmarks here
        Debug::output(1);

    }

}