<?php
/**
 * date utility
 *
 */
namespace CoreORM\Utility;
class Date
{
    const DATE_FORMAT_MYSQL = 'Y-m-d';
    const TIME_FORMAT_MYSQL = 'H:i:s';
    const DATE_TIME_FORMAT_MYSQL = 'Y-m-d H:i:s';
    const DATE_FORMAT_DISPLAY = 'M j, Y';
    const TIME_FORMAT_DISPLAY = 'H:i';
    const DATE_TIME_FORMAT_DISPLAY = 'M j, Y H:i';
    const MYSQL_DATE_FORMAT_DISPLAY = '%b %d, %Y';
    const MYSQL_TIME_FORMAT_DISPLAY = '%H:%i';
    const MYSQL_DATE_TIME_FORMAT_DISPLAY = '%b %d, %Y %H:%i';
    const ZEND_DATE_FORMAT_MYSQL = 'yyyy-MM-dd';
    const ZEND_DATE_TIME_FORMAT_MYSQL = 'yyyy-MM-dd HH:mm:ss';
    const ZEND_TIME_FORMAT_MYSQL = 'HH:mm:ss';


    /**
     * get data time formatted by given format
     *
     * @param string $val    the name of the variable
     * @param string $format  format of date, such as jS, F Y...
     * @param string $default default value
     *
     * @return string
     */
    public static function formatDatetime($val, $format, $default = null)
    {
        if (empty($val)  || $val == '0000-00-00 00:00:00' || $val == '0000-00-00') {
            return $default;
        }
        // if format is false, then return raw...
        if ($format === false) {
            return $val;
        }
        // otherwise, calculate it..
        $datetime = strtotime($val);
        return date($format, $datetime);

    }// end formatDatetime

}// end Date
