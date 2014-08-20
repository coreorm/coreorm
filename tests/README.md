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

For sqlite database, just make sure support/tmp is writable, then go to support directory, run
```
php sqlitesetup.php
```