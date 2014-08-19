<?php
/**
 * mysql adaptor
 * @uses Pdo
 */
namespace CoreORM\Adaptor;
use CoreORM\Core;
use CoreORM\Exception\Adaptor;

class MySQL extends Pdo
{
    public function __construct($options = array())
    {
        if (!isset($options['host']) ||
            !isset($options['user']) ||
            !isset($options['pass']) ||
            !isset($options['dbname'])) {
            throw new Adaptor('Option is invalid');
        }
        $dsn = "mysql:dbname={$options['dbname']};host={$options['host']}";
        if (isset($options['port']) && !empty($options['port'])) {
            $dsn .= ':' . $options['port'];
        }
        // we don't want more than 1 connection to the db...
        $key = md5($dsn . $options['user'] . $options['pass']);
        try {
            $pdo = Pdo::getPdoAdaptor($key);
            if (!$pdo instanceof \PDO) {
                $pdo = new \PDO($dsn, $options['user'], $options['pass']);
                $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);
                // enable pdo buffering.
                $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
                $pdo->exec("SET NAMES 'utf8';");
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
            $name = str_replace('.', '`,`',  $name);
        }
        return "`$name`";

    }// end nameQuote


    /**
     * describe table
     * @param string $tableName name of the table
     * @return array
     */
    public function describe($tableName)
    {
        return (array) $this->fetchAll('DESCRIBE ' . $this->nameQuote($tableName));

    }// end describe

}// end MySQL
?>