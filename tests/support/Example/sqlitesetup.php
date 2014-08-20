<?php
/**
 * this sets up the sqlite file
 */
require_once __DIR__ . '/../../cases/header.php';

$file = realpath(__DIR__ . '/../tmp') . '/model_test.sqlite';
$sqliteDb = new \CoreORM\Adaptor\Sqlite(array(
   'dbname' => $file
));
// test if already there...
$user = $sqliteDb->describe('user');
empty($user) or exit('Db is already setup, you may start testing' . PHP_EOL);
// next, setup file
$sqlFile = __DIR__ . '/model_test.sqlite.sql';
$sql = file_get_contents($sqlFile);
if (!empty($sql)) {
    $sqls = explode(';', $sql);
    foreach ($sqls as $sql) {
        $sql = trim($sql);
        if (!empty($sql)) {
            $stmt = $sqliteDb->query($sql);
        }
    }
}
//\CoreORM\Utility\Debug::output();
exit('Db is setup now, you may start testing' . PHP_EOL);