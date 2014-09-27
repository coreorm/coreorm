#!/bin/bash
export SET ENABLE_DEBUG=no

echo 'Core Tests'
echo '============='
./phpunit cases/core
echo ''

echo 'Adaptor Tests'
echo '============='
./phpunit cases/adaptor
echo ''

echo 'ORM Tests [sqlite]'
echo '=================='
./phpunit cases/orm-sqlite
echo ''

echo 'ORM Tests [mysql]'
echo '=================='
./phpunit cases/orm-mysql

echo 'ORM Tests [model]'
echo '=================='
./phpunit cases/model

echo 'ORM Tests [Dynamodb]'
echo '=================='
./phpunit cases/orm-dynamo

echo 'Adaptor Tests [Dynamodb]'
echo '=================='
./phpunit cases/adaptor-dynamo


export SET ENABLE_DEBUG=yes

