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

export SET ENABLE_DEBUG=yes