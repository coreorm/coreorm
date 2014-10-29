<?php
/**
 * debug static utility
 *
 */

namespace CoreORM\Utility;
use CoreORM\Adaptor\Dynamodb;
use CoreORM\Adaptor\MySQL;
use CoreORM\Adaptor\Pdo;
use CoreORM\Core;

class Debug
{
    // constants
    const BENCH    = 'bench';
    const PROFILER = 'mysql_profile';
    const USER = 'user_data';   // user data - free input

    /**
     * debug setting
     * @var bool
     */
    public static $DEBUG = false;

    /**
     * data
     * @var array
     */
    public static $DATA = array();

    /**
     * debug settings
     * @param null $debug
     * @return bool
     */
    public static function debug($debug = null)
    {
        if ($debug === true) {
            self::$DEBUG = true;
            return true;
        }
        if ($debug === false) {
            self::$DEBUG = false;
            return false;
        }
        return self::$DEBUG;

    }// end debug


    /**
     * dump variables - a handy replacement of var_dump
     * can take infinite params
     * @return void
     */
    public static function dump()
    {
        // no dump if not debug mode
        if (!self::debug()) {
            return;
        }
        // this takes infinite number of arguments
        $args  = func_get_args();// we only need to go back to 2 traces to find out...
        $src   = null;
        $trace = (array) debug_backtrace();
        foreach ($trace as $line) {
            if (!empty($line['file']) && !empty($line['function']) && $line['function'] == 'dump') {
                $src = "Line {$line['line']} in [{$line['file']}]";
                break;
            }
        }
        $total = count($args);
        $s     = ($total > 1) ? 's' : null;
        $src   = $total . ' variable' . $s . ' dumped at ' . $src;
        // cli? just simply dump the stuff out
        if ((php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))) {
            echo PHP_EOL . $src . PHP_EOL . str_repeat('-', strlen($src)) . PHP_EOL;
            foreach ($args as $arg) {
                echo '(' . gettype($arg) , ') ';
                var_dump($arg);
                // add new line
                echo PHP_EOL;
            }
            return;
        }
        // otherwise, a proper dump!
        echo '<div style="font:bold 11px Courier New; color:#333; background:#fff; line-height:1.3;">' .
             $src . '</div>' . self::export($args);

    }// end dump


    /**
     * export an array of arguments
     * @param array $args;
     * @return array
     */
    public static function export($args)
    {
        $str = '<div style="width:100%;clear:both;">';
        foreach ($args as $arg) {
            if ($arg instanceof \Exception) {
                self::dumpException($arg);
            } else {
                $str .= '<pre>';
                $arg = self::printVar($arg);
                // put html tags into proper entities to show on page!
                if (strpos($arg, '<') !== false && strpos($arg, '>') !== false) {
                    // this is html code!
                    $arg = htmlentities($arg);
                }
                $str .= $arg . "\n";
                $str .= '</pre>';
            }
        }
        return $str . '</div>';

    }// end export


    /**
     * the exception handler
     * @param \Exception $e
     * @return void (will print out)
     */
    public static function dumpException(\Exception $e)
    {
        // do not show when unit test is not on
        if (!self::debug()) {
            return;
        }
        // we want a nicely done table for this
        $styleTh = 'style="text-align:right;background:#eee;padding:4px;"';
        $styleTd = 'style="text-align:left;background:#fff;padding:4px;"';
        // the output
        $src = '<div style="font:11px Courier New;text-align:left;background:#fff;">
                <h3 style="font-size:14px;">' . get_class($e) . ': ' . $e->getMessage() . '</h3>
                <table style="font:11px Courier New;background:#999;" border="0" cellpadding="0" cellspacing="1">
                <tr><th ' . $styleTh . '>file</th><td ' . $styleTd . '>' . $e->getFile() . '</td></tr>
                <tr><th ' . $styleTh . '>code</th><td ' . $styleTd . '>' . $e->getLine() . '</td></tr>
                <tr><th ' . $styleTh . '>line</th><td ' . $styleTd . '>' . $e->getCode() . '</td></tr>';
        $traces = $e->getTrace();
        foreach ($traces as $k => $trace) {
            $src .= '<tr><th colspan="2" style="color:#fff;padding:4px;">Trace #' . ($k + 1) . '</th></tr>';
            foreach ($trace as $n => $v) {
                // check if v is array of objects, if so, DO NOT EXPORT ALL!
                if ($n == 'args' && is_array($v)) {
                    // just export type if it's object v
                    $v = self::printVar($v);
                } else {
                    $v = (string) $v;
                }
                $src .= '<tr><th ' . $styleTh . '>' . $n . '</th><td ' . $styleTd . '><pre>' . $v . '</pre></td></tr>';
            }
            unset($v);
            unset($trace);
        }
        $src .= '</table></div>';
        echo $src;
        return false;

    }// end exception_handler


    /**
     * bench mark a function
     * use pass thru for the function
     * @param $method
     * @param array $params
     * @param null $class
     * @return mixed
     */
    public static function bench($method, $params = array(), $class = null)
    {
        // figure out what type of params is available
        if (!is_array($params)) {
            $params = array($params);
        }
        if (!empty($class) && (is_object($class) || class_exists($class))) {
            $call = array($class, $method);
        } else {
            $call = $method;
        }
        // don't bench it if debug is not allowed
        if (!self::debug()) {
            // otherwise, get as many params
            return call_user_func_array($call, $params);
        }
        // start mtime tracking and memory usage tracking
        $sTime = microtime(true);
        // call function
        $result = call_user_func_array($call, $params);
        $eTime  = microtime(true);
        $mem    = memory_get_usage(true);
        // log it
        self::benchLog($eTime - $sTime, $mem, $method, $params, $class);
        // return the original result
        return $result;

    }// end startBench


    /**
     * log bench
     * @param $time
     * @param $mem
     * @param $method
     * @param array $params
     * @param null $class
     * @return array
     */
    protected static function benchLog($time, $mem, $method, $params = array(), $class = null)
    {
        if (!self::debug()) return;
        // class name...
        if (!empty($class)) {
            $classStr = is_object($class) ? get_class($class) : (string) $class . ' (static)';
        } else {
            $classStr = 'N/A';
        }
        $duration = number_format($time, 4);
        $mem = number_format($mem / (1024 * 1024), 2) . 'M';
        // if class is adaptor, use special one for profiling
        if ($class instanceof Pdo || $class instanceof Dynamodb) {
            return self::$DATA[self::PROFILER][] = array(
                'CLASS'  => $classStr,
                'SQL' => self::printVar($params[0]),
                'BIND' => !empty($params[1]) ? $params[1] : null,
                'DURATION' => $duration,
                'MEM' => $mem
            );
        }
        // this is only for benchmarking
        return self::$DATA[self::BENCH][] = array(
            'CLASS'  => $classStr,
            'METHOD' => $method,
            'PARAMS' => $params,
            'DURATION' => $duration,
            'MEM' => $mem
        );

    }// end benchLog


    /**
     * get the bench results
     * for sql
     * @param bool $simple if true, use simple output, skip the params
     * @return void
     */
    public static function sqlProfile($simple = false)
    {
        if (!self::debug()) return;
        if (empty(self::$DATA[self::PROFILER])) {
            return;
        }
        $tbl = new Console_Table();
        $tbl->setHeaders(array('Sql', 'Duration', 'Bind'));
        $cnt = 0;
        foreach (self::$DATA[self::PROFILER] as $row) {
            if ($cnt > 0) {
                $tbl->addSeparator();
            }
            $bind = self::printVar($row['BIND']);
            if ($simple) {
                $sql = substr($row['SQL'], 0, 500);
                if ($row['SQL'] != $sql) {
                    $sql .= '...';
                }
                $row['SQL'] = $sql;
            }
            $tbl->addRow(array($row['SQL'], $row['DURATION'], $bind));
            $cnt ++;
        }
        echo PHP_EOL . $tbl->getTable() . PHP_EOL;

    }// end sqlProfile


    /**
     * set user data
     * NOTE: only key/value pairs are allowed
     * also new data WON'T override existing one, all
     * kept in history
     * @param $key
     * @param $value
     *
     */
    public static function setUserData($key, $value)
    {
        if (!self::debug()) return;
        // get debug trace (so we know where it's setting it)
        $trace = (array) debug_backtrace();
        $src = null;
        foreach ($trace as $line) {
            if (!empty($line['file']) && !empty($line['function']) && $line['function'] == __FUNCTION__) {
                $src = "Line {$line['line']} in File [{$line['file']}]";
                break;
            }
        }
        if (!is_string($value) && !is_numeric($value)) {
            $value = self::printVar($value);
        }
        self::$DATA[self::USER][] = array($key, $value, $src);

    }// end setUserData


    /**
     * bench result
     * @param bool $simple if true, use simple output, skip the params
     */
    public static function benchResult($simple = false)
    {
        if (!self::debug()) return;
        if (empty(self::$DATA[self::BENCH])) {
            return;
        }
        $tbl = new Console_Table();
        $tbl->setHeaders(array('Class', 'Method', 'params', 'duration', 'memory'));
        $cnt = 0;
        foreach (self::$DATA[self::BENCH] as $row) {
            if ($cnt > 0) {
                $tbl->addSeparator();
            }
            if ($simple && !empty($row['PARAMS'])) {
                $tmp = array();
                foreach ($row['PARAMS'] as $p) {
                    if (is_object($p) || is_array($p)) {
                        $tmp[] = gettype($p);
                    } else {
                        $tmp[] = $p;
                    }
                }
                $row['PARAMS'] = $tmp;
            }
            $row['PARAMS'] = self::printVar($row['PARAMS']);
            $tbl->addRow($row);
            $cnt ++;
        }
        echo PHP_EOL . $tbl->getTable() . PHP_EOL;

    }// end benchResult


    /**
     * custom user info
     */
    public static function userInfo()
    {
        if (!self::debug()) return;
        if (empty(self::$DATA[self::USER])) {
            return;
        }
        $tbl = new Console_Table();
        $tbl->setHeaders(array('key', 'value', 'src'));
        $cnt = 0;
        foreach (self::$DATA[self::USER] as $row) {
            if ($cnt > 0) {
                $tbl->addSeparator();
            }
            $tbl->addRow($row);
            $cnt ++;
        }
        echo PHP_EOL . $tbl->getTable() . PHP_EOL;
    }


    /**
     * convert to string output
     * @param bool $simple if true, use simple output, skip the params
     * @return void
     */
    public static function output($simple = false)
    {
        if (!self::debug()) return;
        echo PHP_EOL . 'USER INFO';
        self::userInfo();
        echo PHP_EOL . 'BENCHMARKS';
        self::benchResult($simple);
        echo PHP_EOL . 'SQL PROFILER';
        self::sqlProfile($simple);

    }// end output


    /**
     * export one variable
     * @param $var
     * @return string
     */
    protected static function printVar($var)
    {
        ob_start();
        print_r($var);
        return ob_get_clean();

    }

}// end Debug
