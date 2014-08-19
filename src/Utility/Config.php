<?php
/**
 * debug static utility
 *
 */
namespace CoreORM\Utility;

class Config
{
    /**
     * current config array
     * @var array
     */
    protected static $data = array();


    /**
     * setter
     * @param $key
     * @param $val
     */
    public static function set($key, $val = null)
    {
        if (is_array($key)) {
            return self::setArray($key);
        }
        self::$data[$key] = $val;

    }// end set


    /**
     * getter
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // support dot syntax
        return Assoc::get(self::$data, $key, $default);

    }// end get


    /**
     * set array of conf
     * @param $array
     */
    public static function setArray($array)
    {
        self::$data = Assoc::merge(self::$data, $array);

    }// end setArray


    /**
     * export all data
     * @return array
     */
    public static function export()
    {
        return self::$data;

    }// end export

}// end Config
