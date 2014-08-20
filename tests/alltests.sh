#!/bin/bash
echo 'Core Tests'
echo '============='
./phpunit cases/core

echo 'Adaptor Tests'
echo '============='
./phpunit cases/adaptor

echo 'Model Tests'
echo '============='
./phpunit cases/model