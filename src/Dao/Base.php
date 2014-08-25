<?php
/**
 * the core of the entire structure
 * used to store temporary objects
 * at run time,
 * as well as providing handy debug functions
 */
namespace CoreORM\Dao;

use CoreORM\Adaptor\MySQL;
use CoreORM\Adaptor\Pdo;
use CoreORM\Adaptor\Sqlite;
use CoreORM\Exception\Dao;
use CoreORM\Utility\Assoc;
use CoreORM\Exception\Adaptor;
use CoreORM\Utility\Config;
use CoreORM\Utility\Debug;
use SebastianBergmann\Exporter\Exception;

class Base
{
    /**
     * cache all connectors
     * within the class
     * @var array
     */
    static protected $adaptors = array();

    /**
     * master db name, only set once
     * @var string
     */
    protected $masterDbName = null;

    /**
     * the slave db
     * @var string
     */
    protected $slaveDbName = null;


    /**
     * is slave valid?
     * 1: valid, 0: not tested yet, -1: invalid
     * @var int
     */
    private $slaveValid = 0;


    /**
     * init function for anything to call
     * upon class instantiation
     */
    public function init(){}

    /**
     * constructor
     * @param string $name
     * @param array $options
     */
    public function __construct($name = null, $options = array())
    {
        $this->setMaster($name, $options);
        // call init now
        $this->init();

    }// end __construct

/*-----------------[ adaptors ]-----------------*/

    /**
     * setup the master adaptor
     * for the current db
     * @param $name
     * @param array $options
     * @throws \CoreORM\Exception\Dao
     */
    public function setMaster($name, $options = array())
    {
        if (empty($name)) {
            // use default
            $name = getDbConfig('default');
            // still empty?
            if (empty($name)) {
                throw new Dao("Database name is required or run setDbConfig('default', " .
                              " default_db_name) to set default");
            }
        }
        // first of all - if adaptor is already setup, don't bother doing it again
        if ($this->masterDbName == $name || $this->slaveDbName == $name) {
            // we already have one of these adaptors
            return;
        }
        // we need to test connection before we can use it
        // and for performance concern, we only verify once...
        $this->masterDbName = $name;
        // store options for later (we don't create the adaptor until we use it)
        // NOTE; this options override any previously stored options for this dbname
        if (!empty($options)) {
            Config::set(array(
                'database' => array(
                    $name => $options,
                ),
            ));
        } else {
            $options = Config::get('database.' . $name);
        }
        // set up slave db if applicable
        if (!empty($options['slave'])) {
            $this->setSlave($options['slave']);
        }

    }// end setReadonlySlave


    /**
     * setup a readonly slave
     * for the current db
     * @param $name
     */
    public function setSlave($name)
    {
        // we need to test connection before we can use it
        // and for performance concern, we only verify once...
        $this->slaveDbName = $name;

    }// end setSlave


    /**
     * slave adaptor
     * will revert back to normal adaptor
     * when slave is NOT connecting
     * @return Pdo
     */
    public function slaveAdaptor()
    {
        // if no slave, exit
        if (empty($this->slaveDbName)) {
            Debug::setUserData('slave-adapter offline', 'No slave defined for ' . $this->masterDbName);
            return $this->adaptor();
        }
        // NOTE: we only ever test connection once
        if ($this->slaveValid == 1) {
            // valid
            return $this->adaptor($this->slaveDbName);
        }
        if ($this->slaveValid == -1) {
            // invalid
            return $this->adaptor($this->masterDbName);
        }
        // now we test if connect
        try {
            $adaptor = $this->adaptor($this->slaveDbName);
            $sql = 'SELECT 1';
            $adaptor->query($sql);
            // if the code runs here, it's all good
            Debug::setUserData('slave-adapter online', $this->slaveDbName);
            $this->slaveValid = 1;
            return $adaptor;
        } catch (\Exception $e) {
            $this->slaveValid = -1;
            Debug::setUserData('slave-adapter offline', $this->slaveDbName . ' - ERROR: ' . $e->__toString());
            return $this->adaptor($this->masterDbName);
        }


    }// end slaveAdaptor


    /**
     * get the adaptor by name
     * if NOT exist just init a new one
     * @param null $name
     * @return Pdo
     * @throws \CoreORM\Exception\Dao
     */
    public function adaptor($name = null)
    {
        if (empty($name)) {
            $name = $this->masterDbName;
            if (empty($name)) {
                throw new Dao("Database name is required or run setDbConfig('default', " .
                    " default_db_name) to set default");
            }
        }
        // next, lazy loading
        if (!empty(self::$adaptors[$name])) {
            $existingAdaptor = self::$adaptors[$name];
            if ($existingAdaptor instanceof Pdo) {
                return $existingAdaptor;
            }
        }
        // otherwise, we need to init a new one
        $options = getDbConfig('database.' . $name);
        if (empty($options)) {
            throw new Dao("Database configuration not set for {$name}, please use" . PHP_EOL .
                          "setDbConfig(array(" . PHP_EOL .
                          "    'db name' => (array) options" . PHP_EOL .
                          "));" . PHP_EOL .
                          "to set it up");
        }
        // next, build up the adaptor
        // next, build it up
        $type = Assoc::get($options, 'adaptor');
        if (empty($type)) {
            throw new Dao('$options[adaptor] is not found for db: ' . $name . var_export($options, 1));
        }
        $adaptor = null;
        // next, figure out which adaptor
        switch($type) {
            case Pdo::ADAPTOR_MYSQL:
                $adaptor = new MySQL($options);
                break;
            case Pdo::ADAPTOR_SQLITE:
                $adaptor = new Sqlite($options);
                break;
        }
        if (empty($adaptor)) {
            throw new Dao('Invalid adaptor type: ' . $type);
        }
        // next, set to self
        self::$adaptors[$name] = $adaptor;
        unset($adaptor);
        Debug::setUserData('New Adaptor online', $name . ' (' . $type . ')');
        return self::$adaptors[$name];

    }// end adaptor


    /**
     * Purge all adaptors (in case some lose connection)
     * NOTE: this is only useful in CLI mode, such as a daemon
     */
    public static function purgeAdaptors($name = null)
    {
        // 1st, kill all adaptors
        foreach (self::$adaptors as &$adaptor) {
            $adaptor = null;
        }
        // clear itself
        self::$adaptors = array();
        // next, purge all pdo
        Pdo::purgePdoAdaptor();

    }// end purgeAdaptors

/*-----------------[ CRUD ]-----------------*/

    /**
     * pass through
     * so we can benchmark it
     * @param $sql
     * @param array $bind
     * @param bool $useSlave
     * @return \PDOStatement
     */
    public function query($sql, $bind = array(), $useSlave = false)
    {
        $adaptor = $useSlave ? $this->slaveAdaptor() : $this->adaptor();
        return $this->adaptor()->query($sql, $bind);

    }// end query


    /**
     * insert into table
     * @param array $data the raw data
     * @param string $tableName the table name
     * @return bool
     */
    public function insert($data, $tableName)
    {
        return $this->adaptor()->insert($data, $tableName);

    }// end insert


    /**
     * replace into the db...
     * @param array $data the source data
     * @param string $tableName the actual table name
     * @return bool
     */
    public function replaceInto($data, $tableName)
    {
        return $this->adaptor()->replaceInto($data, $tableName);

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
        return $this->adaptor()->update($data, $tableName, $condition);

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
        return $this->adaptor()->delete($tableName, $condition, $bind);

    }// end delete


    /**
     * fetch one line
     * @param $sql
     * @param array $bind
     * @param bool $useSlave
     * @return mixed
     */
    public function fetchOne($sql, $bind = array(), $useSlave = false)
    {
        $adaptor = $useSlave ? $this->slaveAdaptor() : $this->adaptor();
        return $adaptor->fetchOne($sql, $bind);

    }// end fetchOne


    /**
     * fetch a row
     * @param $sql
     * @param array $bind
     * @param bool $useSlave
     * @return mixed
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchRow($sql, $bind = array(), $useSlave = false)
    {
        $adaptor = $useSlave ? $this->slaveAdaptor() : $this->adaptor();
        return $adaptor->fetchRow($sql, $bind);

    }// end fetchRow


    /**
     * fetch all fetchColumn
     * @param $sql
     * @param array $bind
     * @param bool $useSlave
     * @return mixed|string
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchColumn($sql, $bind = array(), $useSlave = false)
    {
        $adaptor = $useSlave ? $this->slaveAdaptor() : $this->adaptor();
        return $adaptor->fetchColumn($sql, $bind);

    }// end fetchColumn


    /**
     * fetch all
     * @param $sql
     * @param array $bind
     * @param bool $useSlave
     * @return array|mixed
     * @throws \CoreORM\Exception\Adaptor
     */
    public function fetchAll($sql, $bind = array(), $useSlave = false)
    {
        $adaptor = $useSlave ? $this->slaveAdaptor() : $this->adaptor();
        return $adaptor->fetchAll($sql, $bind);

    }// end fetchAll


    /**
     * get row count
     * @return int
     */
    public function getRowCount()
    {
        return $this->adaptor()->getRowCount();

    }// end getRowCount


    /**
     * describe table
     * @param string $tableName name of the table
     * @return array
     */
    public function describe($tableName)
    {
        if (method_exists($this->adaptor(), 'describe')) {
            return $this->adaptor()->describe($tableName);
        }

    }// end describe


    /**
     * test connection
     */
    public function testConnection()
    {
        $this->adaptor()->query('SELECT 1');

    }// end testConnection

}// end Base
?>