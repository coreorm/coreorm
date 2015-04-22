<?php
/**
 * The library core
 * @package com.coreorm
 * @author Bruce B Li
 */
namespace CoreORM
{
    use CoreORM\Integration\Laravel;
    use CoreORM\Integration\Slim;

    /**
     * Class Core
     * @package CoreORM
     */
    class Core
    {
        /**
         * storage for objects
         * @var array
         */
        protected static $OBJECTS = array();

        /**
         * in memory storage
         * @var array
         */
        protected static $STORE = array();

        /**
         * this is where we can get
         * singleton classes
         * NOTE: this won't work for classes with initial inputs
         * @param $className
         * @return mixed
         * @throws \Exception
         */
        public static function singleton($className)
        {
            // register to self
            $key = '_SINGLETON_' . $className;
            if (!empty(self::$OBJECTS[$key]) && self::$OBJECTS[$key] instanceof $className) {
                return self::$OBJECTS[$key];
            }
            // verify if classname is valid
            if (!class_exists($className)) {
                throw new \Exception('Unable to locate file for class: ' . $className);
            }
            // otherwise, create an instance and register
            self::$OBJECTS[$key] = new $className;
            return self::$OBJECTS[$key];

        }// end singleton


        /**
         * key value storage
         * @param $key
         * @param $val
         */
        public static function store($key, $val)
        {
            self::$STORE[$key] = $val;

        }// end store


        /**
         * retrieve by key
         * @param $key
         * @param null $default
         * @return mixed
         */
        public static function retrieve($key, $default = null)
        {
            return !empty(self::$STORE[$key]) ? self::$STORE[$key] : $default;

        }// end retrieve


        /**
         * one line integration with laravel
         * fully automated integration tool
         * @param bool $debug
         * if debug is enabled, it will report uncompatible
         * database adaptors
         */
        public static function integrateWithLaravel($debug = false)
        {
            $integration = new Laravel();
            if ($debug) {
                $integration->reportErrors();
            }

        }

        /**
         * integrate with slim
         * @param \Slim\Slim $app
         * @param bool $debug
         */
        public static function integrateWithSlim(\Slim\Slim $app, $debug = false)
        {
            $opts = array(
                'coreorm' => $app->config('coreorm'),
                'debug' => $app->config('debug'),
            );
            $integration = new Slim($opts);
            if ($debug) {
                $integration->reportErrors();
            }

        }

    }// end Core

}

namespace
{
    use CoreORM\Core;
    use CoreORM\Utility\Config;

    function singleton($className)
    {
        return Core::singleton($className);
    }

    /**
     * quick function for dump
     * @return mixed
     */
    function dump()
    {
        if (CoreORM\Utility\Debug::debug()) {
            $params = (array) func_get_args();
            return call_user_func_array(array('CoreORM\Utility\Debug', 'dump'), $params);
        }

    }

    /**
     * enable debug
     * @param mixed $enabled
     * @return bool
     */
    function CoreDebug($enabled = null)
    {
        return CoreORM\Utility\Debug::debug($enabled);
    }

    /**
     * set config
     * @param $key
     * @param $val
     */
    function setDbConfig($key, $val = null)
    {
        Config::set($key, $val);
    }

    /**
     * set default db
     * @param $key
     */
    function setDefaultDb($key)
    {
        Config::set('default_database', $key);
    }

    /**
     * get config
     * @param $key
     * @param null $default
     * @return mixed
     */
    function getDbConfig($key, $default = null)
    {
        return Config::get($key, $default);
    }

}
