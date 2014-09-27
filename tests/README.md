Modeller/DAO Tests
==================

### setup

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

For mysql database, create new db:
 ```
 model_test
 ```
 then setup user
 ```
 model
 ```
 and password
 ```
 test
 ```
then import the following sql file into this database
```
support/Example/model_test.mysql.sql
```
Now you can run
```
./modeller tests/support/Example/config.mysql.php
```

Note: if you don't want to test model generator, you can avoid setting up sqlite, as long as the directory /support/tmp is writable, it will be just fine.

Otherwise, For sqlite database, just make sure support/tmp is writable, then go to support directory, run
```
php sqlitesetup.php
```
