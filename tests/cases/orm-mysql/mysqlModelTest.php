<?php
/**
 * pdo adaptor
 *
 */
require_once __DIR__ . '/../header.php';
use CoreORM\Model;
$modelDir = realpath(__DIR__ . '/../../support/Example/Model') . '/';
require_once $modelDir . 'User.php';
require_once $modelDir . 'Combination.php';
require_once $modelDir . 'Login.php';
require_once $modelDir . 'File.php';
use Example\Model\User;
use \Example\Model\File;
use \CoreORM\Utility\Debug;
use Example\Model\Combination;
/**
 * test core
 */
class TestMysqlModel extends PHPUnit_Framework_TestCase
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
                "host" => "127.0.0.1",
                "port" => 3306,
            )
        ));
        setDbConfig('default_database', 'orm_test');
        $this->dao = new \CoreORM\Dao\Orm();
        // clear all test data
        $sql = "
        DELETE FROM `attachment`;
        DELETE FROM `user`;
        DELETE FROM `login`;
        DELETE FROM `combined_key_table`;

        INSERT INTO `attachment` (`id`, `user_id`, `filename`, `size`)
        VALUES
          (1,1,'test.jpg',23),
          (2,1,'abc.pdf',34.21),
          (3,2,'low.mov',3020.32),
          (4,3,'page.txt',302.12),
          (5,2,'flow.diagram',23.11);

        INSERT INTO `login` (`user_id`, `username`, `password`)
        VALUES
        (1,'jayf','asfsafadf'),
          (2,'brucel','ljalfasdf');

        INSERT INTO `user` (`id`, `name`, `address`, `birthdate`)
        VALUES
          (1,'Jay Faye','80 Illust Rd. Sydney','1981-03-21'),
          (2,'Bruce L','300 Pitt, Sydney','1977-02-21'),
          (3,'Fry Steve','1 Infinite Loop, Redmond','1972-11-23');
        ";
        $this->dao->query($sql);
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
        $users = Debug::bench('readModels', array($user, array('limit' => 1)), $this->dao);
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

        // test batch write
        $models = array();
        $name = "TEST::'NAME'";
        for ($i = 1; $i <= 5; $i ++) {
            $user = new User();
            $user->setName($name)
                 ->setAddress('ADDRESS ' . $i);
            $models[$i] = $user;
        }
        // normal batch
        $results = $this->dao->writeModels($models, 2, true);
        $this->assertNotEmpty($results);
        // 1 batch
        $results = $this->dao->writeModels($models, 10, true);
        $this->assertNotEmpty($results);
        // this is an individual operation on batch
        $results = $this->dao->writeModels($models, 0, true);
        $this->assertNotEmpty($results);
        $this->assertNotEmpty(current($models)->primaryKey(true));
        Debug::setUserData('Results', $results);

        // output benchmarks here
        Debug::output(1);

    }

    public function testReadAdvanced()
    {
        // 1st test order
        $attachments = $this->dao->readModels(
            new File(),
            array(
                'orderBy' => array(File::FIELD_SIZE => 'DESC'),
                'limit' => 3
            )
        );
        $this->assertTrue(count($attachments) == 3);
        $currentFSize = 0;
        foreach ($attachments as $file) {
            if ($file instanceof File) {
                if (empty($currentFSize)) {
                    $currentFSize = $file->getSize();
                } else {
                    $this->assertGreaterThan($file->getSize(), $currentFSize);
                    $currentFSize = $file->getSize();
                }
            }
        }

    }

}
