<?php
namespace CoreORM\Adaptor;
use CoreORM\Exception\Adaptor;
class Sqlite extends Orm
{
    /**
     * the type
     * @var string
     */
    protected $type = Pdo::ADAPTOR_SQLITE;


    /**
     * the constructor
     * @param array $options
     * @throws Adaptor
     */
    public function __construct($options = array())
    {
        if (!isset($options['dbname'])) {
            throw new Adaptor('dbname is required');
        }
        // we don't want more than 1 connection to the db...
        $key = md5($options['dbname']);
        $dsn = 'sqlite:' . $options['dbname'];
        try {
            $pdo = Pdo::getPdoAdaptor($key);
            if (!$pdo instanceof \PDO) {
                $pdo = new \PDO($dsn);
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
     * replace into the db...
     * not supported by sqlite
     */
    public function replaceInto($data, $tableName)
    {
        throw new \Exception('Replace Into is not supported by SQLite');

    }// end replaceInto


    /**
     * quote name with corresponding enclosure chars
     * NOTE: this is now updated to be compatible with
     * MySQL, if it doesn't work, update your sqlite lib
     * @param string $name the name
     * @return string
     */
    public function nameQuote($name)
    {
        if (strpos($name, '.') !== false) {
            $name = str_replace('.', '`,`',  $name);
        }
        return '`' . $name . '`';

    }// end nameQuote


    /**
     * describe table
     * @param string $tableName name of the table
     * @return array
     */
    public function describe($tableName)
    {
        $details = $this->fetchAll('PRAGMA table_info("' . $tableName . '");');
        /*
         [0] => Array
                (
                    [cid] => 0
                    [name] => id
                    [type] => INTEGER
                    [notnull] => 1
                    [dflt_value] =>
                    [pk] => 1
                )
         */
        // compose on this
        $fields     = array();
        $index      = array();
        $primaryKey = array();
        $all        = array();
        foreach ($details as &$field) {
            // make tmpField the same way as in mysql for unified interface
            $tmpField = array(
                'Field'   => $field['name'],
                'Type'    => $field['type'],
                'Null'    => ($field['notnull'] == 1) ? 'NO' : 'YES',
                'Key'     => null,
                'Default' => $field['dflt_value']
            );

            // set the primary keys...
            if ($field['pk'] == 1) {
                $tmpField['Key'] = 'PRI';
            } elseif ($field['cid'] > 0) {
                $tmpField['Key'] = 'MUL';
            }
            // set it back
            $field = $tmpField;
        }
        return $details;

    }// end describe

}// end Sqlite
