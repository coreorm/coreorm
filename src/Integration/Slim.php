<?php
/**
 * PHP Slim
 * Integration
 *
 * NOTE:
 * this requires setting up
 * conf in slim in the right
 * way
 *
 */
namespace CoreORM\Integration;

use CoreORM\Adaptor\Pdo, CoreORM\Utility\Config, CoreORM\Utility\Assoc;

class Slim extends Base
{
    protected function enableDebug()
    {
        return Assoc::get($this->options, 'debug');
    }

    protected function translateConfig()
    {
        return array(
            'default_database' => Assoc::get($this->options, 'coreorm.default_database'),
            'database' => Assoc::get($this->options, 'coreorm.database'),
        );
    }

}
