Modeller/DAO Tests
==================

### setup
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
