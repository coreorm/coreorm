<?php
/**
 * dynamodb model generator config
 * @NOTE: we won't want to connect to AWS to generate, and also we won't have the full table structure
 * due to the nature of dynamodb (only primary keys/indexes are available in the table structure
 * thus we would need to put all table structure here
 *
 * Also: only 2 types allowed - int/string
 */
$dir = realpath(__DIR__ . '/Model.Dynamo/');
return array(
    'path' => $dir,
    'namespace' => 'Example\\Model',
    'database' => array(
        'adaptor' => \CoreORM\Adaptor\Pdo::ADAPTOR_DYNAMODB
    ),
    'model' => array(
        'test-user' => array(
            'class' => 'User',
            'fields' => array(
                'id' => 'int',
                'name' => 'string',
                'address' => 'string',
                'created_at' => 'int'
            ),
            'keys' => array(
                'id' => 'hash',
                'name' => 'range',
            )
        ),
    )
);
