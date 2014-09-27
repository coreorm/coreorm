CoreORM/Framework
=================

### What is this thing?

Writing data layer is boring, trying to remember all the table names and all the fields in the table is impossible, writing SQL queries is painful and it's so easy to make mistakes!

I'm sure we've all been through this and we probably trying to avoid this as much as we can, yeah I know there a loads of active-record type ORM layers out there for php to use, but really can you remember all the fields? ```$user->find('user_id=123')``` really?!! And ```echo $user->name``` serious? I have to remember all the fields?

Nope, no, definitely not. So here I am introducing the simplified, light-weight and easy to use (well, code-autocompletion anyone?) ORM framework that totally does not suck.

### features
1. Thin models that can be used across different types of data source (MySQL/SQlite, will expand to cover more later)
2. DAO (Data Access Objects) that manages the thin models instead of models carrying the connections.
3. Out-of-the-box slave database support for large enterprise level data managements, supported by super simple APIs ```$dao->readModel(\CoreORM\Model $model, $useSlave = true)```
4. Table relations in database mapped as object relations! ```$passwordHash = $user->relationGetLogin()->getPassword();```
5. Zero SQL necessary! DAOs will automagically compose the SQL (and trust me, it's very fast) using the relations and criteria from the models.
6. Zero code necessary for models! They are actually generated based on a very simple configuration file (more details see the sections below).
7. Awesome performance - since the models are generated with all table/field information stored within the model, there's no need to describe table to generate queries or whatnot.
8. Extensibility - all models are crafted following proper OOD patterns, it's super easy to extend any model to add functionalities (having said that, you really don't need to do this at 90% of the time).
9. Less code, more accuracy (or actually it's 100% accuracy there). Since all tables are presented by models that contain proper getters and setters, you really don't need to remember the field name/table name at all, just type ```$model->get``` and your IDE should just give you a list of options to pick from.
10. You really want to write your own queries still? That's fine too, add your own DAO extending the base DAO, and you are set!

### compatibility
The current version of CoreORM supports the following 2 database systems
* MySQL
* SQLite
Future versions will add PostgreSQL and MS SQL server, etc.

The current version also ships with out of the box Laravel & Slim framework compatibility.

For Laravel, since it contains db config already, simply add the following line in your ```boostrap/start.php```, right before the final line ```return $app```, add the following line:
```
\CoreORM\Core::integrateWithLaravel();
```
That's it!

For Slim framework, since database config is not part of the framework, you will have to add the database config in the following format before calling your Slim app:
```
$config = array(
    'coreorm' => array(
        'default_database' => 'example',
        'database' => array(
          'example' => array(
              'dbname' => 'coreorm_examples',
              'adaptor' => \CoreORM\Adaptor\Pdo::ADAPTOR_MYSQL,
              'user' => 'coreorm',
              'pass' => 'example',
              'host' => '127.0.0.1'
          )
        )
);
$app = new Slim($config);
```
Then right after that, ad the following line:
```
\CoreORM\Core::integrateWithSlim($app);
```
And you are set!

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
Please read the README.md file under ```tests/``` directory.

### setup the config this way (runtime).

```
    setDbConfig('default_database', [default db adaptor name]);  // we need a default always
    // or, alternatively, use
    setDefaultDb([default db adaptor name]);
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

###NOTE: for Dynamodb, please follow Amazon's suggestion, have your credentials saved in
```/home/[your user]/.aws/credentials```
in the following format:
```
[my-dynamo-db]
aws_access_key_id=xxxxx
aws_secret_access_key=yyyyy
```

and ensure that your database setting follows the format:
```
    setDbConfig('database', array(
        'main' => array(
            'profile' => 'my-dynamo-db',
            'region' => 'my aws region',
            'adaptor' => CoreORM\Adaptor\Pdo::ADAPTOR_DYNAMODB
        )
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

***NOTE
Since Dynamodb is not strictly fixed with fields, if you want to have a model with fixed number of fields, please setup
a similar table structure in mysql or sqlite then generate it using the generator, but make sure you update the parent
class to CoreORM\Model\Dynamodb instead of the original one.

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
