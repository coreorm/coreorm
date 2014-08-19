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

}