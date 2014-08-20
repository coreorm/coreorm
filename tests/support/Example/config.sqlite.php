<?php
$dir = realpath(__DIR__ . '/Model.Sqlite/');
return array(
    'database' => array(
        'dbname' => __DIR__ . '/../tmp/model_test.sqlite',
        'adaptor' => CoreORM\Adaptor\Pdo::ADAPTOR_SQLITE
    ),
    'path' => $dir,
    'namespace' => 'Example\\Model',
    'model' => array(
        'user' => array(
            'relations' => [
                array(
                    'table' => 'login',
                    'join' => 'INNER',
                    'type' => 'S',
                    'on' => array(
                        // support multiple on conditions
                        // must be from left => right
                        'id' => 'user_id'
                    ),
                    'condition' => ''
                ),
                array(
                    'table' => 'attachment',
                    'join' => 'LEFT',
                    'type' => 'M',
                    'on' => array(
                        'id' => 'user_id'
                    ),
                    'condition' => ''
                )
            ],
        ),
        'login' => array(
        ),
        'attachment' => array(
            'class' => 'File'   // let's use a different name for the class...
        ),
        'combined_key_table' => array(
            'class' => 'Combination',
            'relations' => [
                array(
                    'table' => 'login',
                    'join' => 'INNER',
                    'type' => 'S',
                    'on' => array(
                        'user_id' => 'user_id'
                    ),
                    'condition' => ''
                ),
                array(
                    'table' => 'user',
                    'join' => 'INNER',
                    'type' => 'S',
                    'on' => array(
                        'user_id' => 'id'
                    ),
                    'condition' => ''
                )
            ],
        )
    )
);