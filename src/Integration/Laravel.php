<?php
/**
 * laravel framework
 * integration class
 *
 */
namespace CoreORM\Integration;

use CoreORM\Adaptor\Pdo, CoreORM\Utility\Config, CoreORM\Utility\Assoc;
// ensure laravel's config is accessible at this moment
if (!class_exists('\Illuminate\Support\Facades\Config')) {
    throw new \Exception('Unable to access laravel config object');
}
// include laravel config function
use \Illuminate\Support\Facades\Config as LConfig;

class Laravel extends Base
{
    protected function enableDebug()
    {
        return LConfig::get('app.debug');
    }

    protected function translateConfig()
    {
        // get laravel db config
        $conf = LConfig::get('database');
        // set the main db config
        $default = Assoc::get($conf, 'default');
        if (empty($default)) {
            $this->errors[] = 'Default database is NOT set';
        }
        $dbConf = array(
            'default_database' => $default,
            'database' => array(),
        );
        // set each of the connections below
        $connections = Assoc::get($conf, 'connections');
        foreach ($connections as $key => $opts) {
            $dbConf['database'][$key] = $this->getAdaptor($opts);
        }
        return $dbConf;

    }

    protected function getAdaptor($opts)
    {
        $driver = Assoc::get($opts, 'driver');
        switch ($driver) {
            case 'mysql':
                return array(
                    'adaptor' => Pdo::ADAPTOR_MYSQL,
                    'dbname'  => Assoc::get($opts, 'database'),
                    'user'  => Assoc::get($opts, 'username'),
                    'pass'  => Assoc::get($opts, 'password'),
                    'host' => Assoc::get($opts, 'host')
                );
                break;
            case 'sqlite':
                return array(
                    'adaptor' => Pdo::ADAPTOR_SQLITE,
                    'dbname'  => Assoc::get($opts, 'database'),
                );
                break;
            // sorry, no other db supported at the moment
            default:
                $this->errors[] = 'Driver [' . $driver . '] is not supported at the moment';
                return array(
                    'adaptor' => $driver,
                );
                break;
        }
    }

}
