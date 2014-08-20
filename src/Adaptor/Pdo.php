<?php
namespace CoreORM\Adaptor;
use CoreORM\Core;
use CoreORM\Exception\Adaptor;
use CoreORM\Utility\Debug;

/**
 * PDO adaptor for DAO
 *
 */
abstract class Pdo
{
    const ADAPTOR_MYSQL = 'MySQL';
    const ADAPTOR_SQLITE = 'Sqlite';

    /**
     * pdo connection object
     * @var \PDO
     */
    protected $pdo = null;


    /**
     * adaptor type
     * @var string
     */
    protected $type = null;


    /**
     * pdo statement object
     * @var \PDOStatement
     */
    protected $stmt = null;

    /**
     * pdo adaptors for quick solutions
     * @var array
     */
    protected static $pdoAdapters = array();


    /**
     * use cache?
     * @var bool
     */
    protected $useCache = false;


    /**
     * this sets the adaptor
     * @param $k
     * @param \PDO $adaptor
     */
    public static function setPdoAdaptor($k, \PDO $adaptor)
    {
        if (empty(self::$pdoAdapters[$k])) {
            self::$pdoAdapters[$k] = $adaptor;
        }

    }// end pdoAdaptor


    /**
     * get the current adaptor type
     * @return string
     */
    public function getType()
    {
        return $this->type;

    }// end getType


    /**
     * get pdo adaptor
     * @param $k
     * @return mixed
     */
    public static function getPdoAdaptor($k = null)
    {
        // if no k provided, return all
        if (empty($k)) {
            return self::$pdoAdapters;
        }
        if (!empty(self::$pdoAdapters[$k])) {
            return self::$pdoAdapters[$k];
        }

    }// end getPdoAdaptor


    /**
     * purge pdo adaptor
     * @param $k
     * @return mixed
     */
    public static function purgePdoAdaptor($k = null)
    {
        if (!empty($k)) {
            if (!empty(self::$pdoAdapters[$k])) {
                self::$pdoAdapters[$k] = null;
            }
        } else {
            foreach (self::$pdoAdapters as &$pdo) {
                $pdo = null;
            }
            self::$pdoAdapters = array();
        }

    }// end getPdoAdaptor


    /**
     * construct the adaptor
     * @param array $options
     */
    public function __construct($options = array())
    {
        // setup cache
        if (!empty($options['cache'])) {
            $this->useCache = (bool) $options['cache'];
        }

    }// end __construct


    /**
     * get the last insert id...
     * @return int
     */
    public function getLastInsertId()
    {
        return (int) $this->pdo->lastInsertId();
        // support: mysql, sqlite, pgsql

    }// end getLastInsertId;


    /**
     * quote names... such as `` or ""
     * @param $name
     * @return mixed
     */
    abstract public function nameQuote($name);


    /**
     * pass through
     * so we can benchmark it
     * @param string $sql the sql
     * @param array $bind the bound array
     * @return \PDOStatement
     */
    public function query($sql, $bind = array())
    {
        if (debug()) {
            return Debug::bench('queryRawPrivate', array($sql, $bind), $this);
        }
        return $this->queryRawPrivate($sql, $bind);

    }// end query


    /**
     * run the query - private but has to be public for speed check...
     * @param $sql
     * @param array $bind
     * @return \PDOStatement
     * @throws \Exception
     */
    public function queryRawPrivate($sql, $bind = array())
    {
        // test
        // AppCore::dump($sql, $bind);
        // prepare...
        try {
            $this->stmt = $this->pdo->prepare($sql);
            if (is_object($this->stmt) && $this->stmt->execute($bind)) {
                // now, do i need to clear buffer?
                if (strpos(strtolower($sql), 'select') === false &&
                    strpos(strtolower($sql), 'show') === false &&
                    strpos(strtolower($sql), 'describe') === false &&
                    strpos(strtolower($sql), 'pragma') === false) {
                    // should clear it!
                    $this->stmt->fetchAll();
                }
                // return the statement
                return $this->stmt;
            }
            // otherwise, check error
            $err = 'unknown PDO error';
            $code = 100;
            if (is_object($this->pdo)) {
                $err  = (array) $this->pdo->errorInfo();
                $code = !empty($err[1]) ? (int) $err[1] : null;
                $err  = !empty($err[2]) ? $err[2] : null;
            }
            if (is_object($this->stmt)) {
                $err  = $this->stmt->errorInfo();
                $err  = !empty($err[2]) ? $err[2] : null;
                $code = $this->stmt->errorCode();
            }
            $e = new Adaptor('SQL ERROR: ' . $err, (int) $code);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;

        } catch (\Exception $e) {
            $e = new Adaptor($e->getMessage(), $e->getCode(), $e);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;
        }

    }// end queryRawPrivate


    /**
     * quote some string
     * @param $str
     * @return mixed
     */
    public function quote($str)
    {
        return $this->pdo->quote($str);

    }// end quote


    /**
     * insert into table
     * @param array $data the raw data
     * @param string $tableName the table name
     * @return bool
     */
    public function insert($data, $tableName)
    {
        // break down...
        $breakDown = $this->breakDownData($data, $tableName, 'insert');
        // run
        $result = $this->query($breakDown['sql'], $breakDown['bind']);
        // insert id...
        $id = (int) $this->getLastInsertId();
        if ($id > 0) {
            return $id;
        }
        return $result;

    }// end insert


    /**
     * replace into the db...
     * @param array $data the source data
     * @param string $tableName the actual table name
     * @return bool
     */
    public function replaceInto($data, $tableName)
    {
        // break down...
        $breakDown = $this->breakDownData($data, $tableName, 'replace_into');
        // run
        return $this->query($breakDown['sql'], $breakDown['bind']);

    }// end replaceInto


    /**
     * update table data
     * @param array $data the source data
     * @param string $tableName the actual table name
     * @param string $condition the condition for udpate, must be set
     * @return bool
     */
    public function update($data, $tableName, $condition)
    {
        // condition must be there!!!
        if (empty($condition)) {
            throw new Adaptor('UPDATE ' . $tableName . ' Condition is empty');
        }
        // break down...
        $breakDown = $this->breakDownData($data, $tableName, 'update');
        // run
        return $this->query($breakDown['sql'] . " WHERE {$condition}", $breakDown['bind']);

    }// end update


    /**
     * delete
     * @param $tableName
     * @param $condition
     * @param array $bind
     * @return mixed
     * @throws \CoreORM\Exception\Adaptor
     */
    public function delete($tableName, $condition, $bind = array())
    {
        // condition must be there!!!
        if (empty($condition)) {
            throw new Adaptor('DELETE FROM ' . $tableName . 'Condition is empty');
        }
        // otherwise, delete!
        return $this->query('DELETE FROM ' . $tableName . ' WHERE ' . $condition . ';', $bind);

    }// end delete


    /**
     * fetch one line
     * @param $sql
     * @param array $bind
     * @return mixed
     * @throws \Exception
     */
    public function fetchOne($sql, $bind = array())
    {
        // cached?
        if ($this->useCache) {
            // try fetching it...
            $data = $this->loadCache($sql, $bind);
            if (!empty($data)) {
                return $data;
            }
        }
        // prepare statement
        try {
            if ($this->query($sql, $bind)) {
                // start fetching
                $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
                $result = $this->stmt->fetch();
                if (!empty($result)) {
                    // because we only fetch one...
                    $result = current($result);
                }
                // if cache...
                if ($this->useCache) {
                    $this->cache($sql, $bind, $result);
                }
                return $result;
            }
            $e = new Adaptor('Fetch One Error');
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;

        } catch (\Exception $e) {
            $e = new Adaptor($e->getMessage(), $e->getCode(), $e);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;
        }

    }// end fetchOne


    /**
     * fetch a row
     * @param $sql
     * @param array $bind
     * @return mixed
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchRow($sql, $bind = array())
    {
        // cached?
        if ($this->useCache) {
            // try fetching it...
            $data = $this->loadCache($sql, $bind);
            if (!empty($data)) {
                return $data;
            }
        }

        // prepare statement
        try {
            if ($this->query($sql, $bind)) {
                // start fetching
                $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
                $result = $this->stmt->fetchAll();
                if (!empty($result)) {
                    $result = current($result);
                }
                // if cache...
                if ($this->useCache) {
                    $this->cache($sql, $bind, $result);
                }
                return $result;
            }
            $e = new Adaptor('Fetch Row Error');
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;

        } catch (\Exception $e) {
            $e = new Adaptor($e->getMessage(), $e->getCode(), $e);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;
        }

    }// end fetchRow


    /**
     * fetch all fetchColumn
     * @param $sql
     * @param array $bind
     * @return mixed|string
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchColumn($sql, $bind = array())
    {
        // cached?
        if ($this->useCache) {
            // try fetching it...
            $data = $this->loadCache($sql, $bind);
            if (!empty($data)) {
                return $data;
            }
        }

        // prepare statement
        try {
            if ($this->query($sql, $bind)) {
                // start fetching
                $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
                $result = array();
                while ($row = $this->stmt->fetchColumn()) {
                    $result[] = $row;
                }
                // if cache...
                if ($this->useCache) {
                    $this->cache($sql, $bind, $result);
                }

                return $result;
            }
            $e = new Adaptor('Fetch Column Error');
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;

        } catch (\Exception $e) {
            $e = new Adaptor($e->getMessage(), $e->getCode(), $e);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;
        }

    }// end fetchColumn


    /**
     * fetch all
     * @param $sql
     * @param array $bind
     * @return array|mixed
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchAll($sql, $bind = array())
    {
        // cached?
        if ($this->useCache) {
            // try fetching it...
            $data = $this->loadCache($sql, $bind);
            if (!empty($data)) {
                return $data;
            }
        }

        // prepare statement
        try {
            if ($this->query($sql, $bind)) {
                // start fetching
                $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
                $result = $this->stmt->fetchAll();
                // if cache...
                if ($this->useCache) {
                    $this->cache($sql, $bind, $result);
                }
                return $result;
            }
            $e = new Adaptor('Fetch All Error');
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;

        } catch (\Exception $e) {
            $e = new Adaptor($e->getMessage(), $e->getCode(), $e);
            $e->sql = $sql;
            $e->bind = $bind;
            throw $e;
        }

    }// end fetchAll


    /**
     * get row count
     * @return int
     */
    public function getRowCount()
    {
        if ($this->stmt instanceof \PDOStatement) {
            return $this->stmt->columnCount();
        }
        return 0;

    }// end getRowCount


    /**
     * cache select only queries
     * @param $sql
     * @param array $bind
     * @param null $result
     * @throws \Exception
     */
    private function cache($sql, $bind = array(), $result = null)
    {
        // verify input
        if (strpos(strtolower($sql), 'select') === false &&
            strpos(strtolower($sql), 'show') === false) {
            throw new Adaptor('[' . $sql . '] is not a `select` query or a `show` query, it can not be cached.');
        }
        Core::store($this->makeKey($sql, $bind), $result);

    }// end cache


    /**
     * load from cached queries...
     * @param string $sql
     * @param array $bind
     * @return mixed
     */
    private function loadCache($sql, $bind = array())
    {
        return Core::retrieve($this->makeKey($sql, $bind));

    }// end loadCache


    /**
     * make key for caching
     * @param string $sql
     * @param array $bind
     * @return string
     */
    private function makeKey($sql, $bind = array())
    {
        return '_SQL_' . md5(serialize(array($sql, $bind)));

    }// end makeKey


    /**
     * break down the data
     * @param $data
     * @param $tableName
     * @param $type
     * @return array
     * @throws \CoreORM\Exception\Adaptor
     */
    private function breakDownData($data, $tableName, $type)
    {
        // break down...
        if (!is_array($data) ||
            empty($data) ||
            empty($tableName)) {
            throw new Adaptor("$type into $tableName Data is invalid");
        }
        // otherwise, do it...
        $fields = array();
        $bind   = array();
        $holder = array();

        foreach ($data as $field => $val) {
            if ($type == 'insert') {
                $fields[] = $this->nameQuote($field);
            } else {
                $fields[] = $this->nameQuote($field) . ' = ?';
            }
            $bind[]   = $val;
            $holder[] = '?';
        }
        $tableName = $this->nameQuote($tableName);
        // implement...
        $sql = null;
        switch ($type) {
            case 'insert':
                $sql = 'INSERT INTO ' . $tableName . '(' . implode(', ', $fields) .
                    ') VALUES (' . implode(', ', $holder) . ')';
                break;
            case 'update':
                $sql = 'UPDATE ' . $tableName . ' SET ' . implode(', ', $fields);
                break;
            case 'replace_into':
                $sql = 'REPLACE INTO ' . $tableName . ' SET ' . implode(', ', $fields);
                break;
        }
        return array('sql' => $sql, 'bind' => $bind);

    }// end breakDownData

}// end Pdo
?>