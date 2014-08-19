CoreORM
=======

PHP Object Relational Mapping package with DAO and managed thin models, supports read/write, multiple adaptors and more.
'''NOTE''' currently only supports MySQL with fully ORM support, but dao can still be used on sqlite.

#### composer
run
    composer install --prefer-dist
to make it faster

#### NOTE: run composer install to enable autoload, if not all files are loading, run
    composer dump-autoload

#### unit tests:
make sure you do
    chmod +x tests/phpunit so it can run tests

also, run the following sql in local (127.0.0.1) mysql instance to test adaptors
    CREATE DATABASE `coreorm` CHARACTER SET utf8;
    USE `coreorm`;
    GRANT all ON `coreorm`.* TO core@localhost IDENTIFIED BY 'test';

// also add a slave user for it so we can simulate slave test
    GRANT select ON `coreorm`.* TO core_slave@localhost IDENTIFIED BY 'test';

to test sqlite, make sure you do the following:
    // under tests folder, run
    mkdir tmp;chmod 775 tmp;

### setup the config this way.
#### run time config using setDbConfig:
    setDbConfig('default', [default db adaptor name]);  // we need a default always
    // next, set up the database adaptors in this way:
    setDbConfig('database', array(
        [adaptor name] => (array) [$options]
    ));
    // use mysql as an example:
    setDbConfig('database', array(
        'main' => array(
            'dbname' => 'my_db_name',
            'user' => 'db_user_name',
            'pass' => 'db_password',
            'host' => '127.0.0.1',
            'port' => 3306, // optional
            'cache' => false, // default is false, true to enable cache in memory - NOTE: will increase memory usage
        ),
        'db1' => array(
            ...
        ),
    ));

#### generating models
Run the following commands at project root
    chmod +x modeller
to generate model, make sure you put a config.php file somewhere, see example below:
    <?php
    $dir = realpath(__DIR__ . '/Model/');
    return array(
        'database' => array(
            'dbname' => 'model_test',
            'user' => 'model',
            'adaptor' => 'MySQL',
            'pass' => 'test',
            'host' => '127.0.0.1'
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
        )
    );

then just run
    ./modeller config.php
all the models will be generated in the directory you specified in the configuration file.


### API:
    dao->writeModel()
    dao->readModel()
    dao->deleteModel()
    dao->readModels()