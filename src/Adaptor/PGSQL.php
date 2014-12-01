<?php
/**
 * mysql adaptor
 * @uses Pdo
 */
namespace CoreORM\Adaptor;
use CoreORM\Exception\Adaptor;

class PGSQL extends Orm
{
    /**
     * the type
     * @var string
     */
    protected $type = Pdo::ADAPTOR_PGSQL;


    /**
     * constructor
     * @param array $options
     * @throws \CoreORM\Exception\Adaptor
     */
    public function __construct($options = array())
    {
        if (!isset($options['host']) ||
            !isset($options['user']) ||
            !isset($options['pass']) ||
            !isset($options['dbname'])) {
            throw new Adaptor('Option is invalid');
        }
        $dsn = "pgsql:host={$options['host']};dbname={$options['dbname']};";
        if (isset($options['port']) && !empty($options['port'])) {
            $dsn .= 'port=' . $options['port'] . ';';
        }
        $dsn .= 'user=' . $options['user'] . ';password=' . $options['pass'];
        // we don't want more than 1 connection to the db...
        $key = md5($dsn);
        try {
            $pdo = Pdo::getPdoAdaptor($key);
            if (!$pdo instanceof \PDO) {
                $pdo = new \PDO($dsn);
                $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);
                // enable pdo buffering.
                $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
                $pdo->query('set names utf8;');
                // store pdo
                Pdo::setPdoAdaptor($key, $pdo);
                unset($pdo);
            }
            // retrieve again...
            $pdo = Pdo::getPdoAdaptor($key);
            $this->pdo = $pdo;
            // set all to parent
            parent::__construct($options);
        } catch (\PDOException $e) {
            // throw our own exception object
            throw new Adaptor($e->getMessage(), $e->getCode(), $e);
        }

    }// end __construct

    /**
     * quote name
     * @param $name
     * @return mixed|string
     */
    public function nameQuote($name)
    {
        if (strpos($name, '.') !== false) {
            $name = str_replace('.', '","',  $name);
        }
        return "\"$name\"";

    }// end nameQuote


    /**
     * describe table
     * @param string $tableName name of the table
     * @return array
     */
    public function describe($tableName)
    {
        $sql = 'select column_name, data_type, character_maximum_length ' .
               'from INFORMATION_SCHEMA.COLUMNS where table_name = ?;';
        return (array) $this->fetchAll($sql, array($tableName));

    }// end describe


    /**
     * break down the data
     * @param $data
     * @param $tableName
     * @param $type
     * @return array
     * @throws \CoreORM\Exception\Adaptor
     */
    protected function breakDownData($data, $tableName, $type)
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
        $result = $this->query($breakDown['sql'] . ' RETURNING id', $breakDown['bind']);
        // insert id...
        $id = (int) $this->getLastInsertId();
        if ($id > 0) {
            return $id;
        }
        return $result;

    }// end insert

}// end PGSQL
?>
