<?php
/**
 * string utility
 *
 */
namespace CoreORM\Utility;

class String
{
    /**
     * get a camel case name
     * @param $name
     * @param string $glue
     * @return null|string
     */
    public static function camelCase($name, $glue = '_')
    {
        $name = strtolower($name);

        if (strpos($name, $glue) !== false) {
            $names = explode($glue, $name);
            $name  = null;
            foreach ($names as $n) {
                $name .= ucfirst($n);
            }
        } else {
            $name = ucfirst($name);
        }

        return $name;

    }// end camelCase


    /**
     * a little template engine, use :name to do the job
     * @param $template
     * @param $options
     * @return mixed
     */
    public static function template($template, $options)
    {
        foreach ((array) $options as $key => $option) {
            $template = str_replace(':' . $key, $option, $template);
        }

        return $template;

    }// end template


    /**
     * shorten a string
     * @param $string
     * @param $len
     * @param string $suffix
     * @return string
     */
    public static function shorten($string, $len, $suffix = '...')
    {
        if ($len >= strlen($string)) {
            return $string;
        }

        return substr($string, 0, $len) . $suffix;

    }// end shorten

}