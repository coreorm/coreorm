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
empty($user) or exit('Db is setup, you may start testing' . PHP_EOL);
// next, setup file
$sqlFile = __DIR__ . '/model_test.sqlite.sql';
$sql = file_get_contents($sqlFile);
$sqliteDb->query($sql);
exit('Db is setup, you may start testing' . PHP_EOL);