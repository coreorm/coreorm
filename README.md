CoreORM/Framework
=================

PHP Object Relational Mapping package with DAO and managed thin models, supports read/write, multiple adaptors and more.
Fully compatible with MySQL and SQLite, in the future I will try to expand support to more RDBS.

### How to include this package in your project
Simple add the following into your composer.json

```
    "require": {
            "coreorm/framework": "*"
    },
    "autoload": {
        "files": [
            "vendor/coreorm/framework/src/Core.php"
        ]
    }
```


### Install dependencies

```
run
    composer install --prefer-dist
```


### NOTE: run composer install to enable autoload, if not all files are loading, run
    composer dump-autoload

### unit tests:
make sure you do
    chmod +x tests/phpunit so it can run tests

also, run the following sql in local (127.0.0.1) mysql instance to test adaptors

```
    CREATE DATABASE `coreorm` CHARACTER SET utf8;
    USE `coreorm`;
    GRANT all ON `coreorm`.* TO core@localhost IDENTIFIED BY 'test';

# also add a slave user for it so we can simulate slave test

    GRANT select ON `coreorm`.* TO core_slave@localhost IDENTIFIED BY 'test';

# to test sqlite, make sure you do the following:
    // under tests/support folder, run
    mkdir tmp;chmod 775 tmp;
```

### setup the config this way (runtime).

```
    setDbConfig('default_database', [default db adaptor name]);  // we need a default always
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
```

### generating models
Run the following commands at project root

```
    chmod +x modeller
```

to generate model, make sure you put a config.php file somewhere, see example below:

```
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
```
copy
```vendor/coreorm/framework/modeller``` to your project root (same level as vendor directory) as it requires autoload.php
from under the vendor/ directory (or, alternatively, if you would like to keep it somewhere else, just open it and ensure
the autoload.php path is correct.

then just run

```
    ./modeller config.php
```

all the models will be generated in the directory you specified in the configuration file.

### Summary of common APIs
* Orm::writeModel(Model $model)
```
$Orm = new Orm();
$model = new User();
$model->setName('John Doe')->setAddress('1 Sydney Rd. Sydney, NSW 2000, Australia);
$Orm->writeModel($model);
```
* Orm::readModel(Model $model, $useSlave = false)
```
$Orm = new Orm();
$model = new User();
$model->setId(123);
$Orm->readModel($model);
echo $model->getName();
```
* Orm::readModels(Model $model, $condition = array(), $bind = array(), $orderBy = array(), $limit = null, $useSlave = false)
```
$Orm = new Orm();
$model = new User();
$models = $Orm->writeModels($model, array(User::Field_NAME . " LIKE '%Jo%'"));
foreach ($models as $user) {
    echo $user->getName();
}
```
* Orm::deleteModel(Model $model)
```
$Orm = new Orm();
$model = new User();
$model->setId(123);
$Orm->deleteModel($model);
```


### Examples:

For examples, visit the [CoreORM\Examples](https://github.com/coreorm/example)
