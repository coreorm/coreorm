<?php
/**
 * model generator utility
 * @NOTE: CLI mode only, requires db connection and configuration file path
 */
namespace CoreORM\Utility;

use CoreORM\Dao\Base;
use CoreORM\Dao\Orm;
use CoreORM\Model;
use CoreORM\Adaptor\Pdo;

class ModelGenerator
{
    public static function main($params = array())
    {
        // check if config is there
        if (empty($params[1])) {
            return self::usage($params[0]);
        }
        // next, load the json file
        $config = $params[1];
        if (!is_file($config)) {
            self::error('Invalid or non-existent config file ' . $config);
        }
        $conf = include $config;
        if (empty($conf)) {
            self::error('Invalid config file ' . $config);
        }
        Config::set($conf);
        unset($conf, $config);
        // next step, read config and start db and more...
        Debug::debug(true);
        // now, start the real thing with this object
        $worker = new ModelGenerator();
        $worker->generate();

    }

    public static function error($msg)
    {
        echo $msg . PHP_EOL;
        exit(1);
    }

    public static function usage($file)
    {
        echo "USAGE:" . PHP_EOL . "{$file} [model config].php" . PHP_EOL;
    }

    public static function msg($msg)
    {
        echo ' > ' . $msg . PHP_EOL;
    }

/*---------- the real worker ----------*/

    /**
     * @var Base
     */
    protected $dao = null;


    public function generate()
    {
        self::msg('verify config');
        $this->verifyConfig();
        self::msg('setup dao');
        $this->setupDao();
        self::msg('read tables');
        $this->getTablesAndGenerateModels();

    }

    public function verifyConfig()
    {
        $database = Config::get('database');
        $path = Config::get('path');
        $ns = Config::get('namespace');
        $models = Config::get('model');
        // database connection
        if (empty($database)) {
            self::error('config does not contain [database]');
        }
        self::msg('> database config verified');
        if (empty($path)) {
            self::error('config does not contain [path]');
        }
        self::msg('> path config verified');
        if (empty($ns)) {
            self::error('config does not contain [namespace]');
        }
        self::msg('> namespace config verified');
        if (empty($models)) {
            self::error('config does not contain [model]');
        }
        // generate classes
        $classes = array();
        foreach ($models as $table => $info) {
            $classes[$table] = Assoc::get($info, 'class');
            if (empty($classes[$table])) {
                $classes[$table] = ucfirst(String::camelCase($table));
            }
        }
        Config::set('class', $classes);
        self::msg('> model config verified');

    }

    public function setupDao()
    {
        // deal with dynamos here
        if (Config::get('database.adaptor') == Pdo::ADAPTOR_DYNAMODB) {
            $this->dao = Pdo::ADAPTOR_DYNAMODB;
            return;
        }
        // otherwise, do the relation db here
        try {
            $this->dao = new Orm('default', Config::get('database'));
            $this->dao->testConnection();
            self::msg('db is online');
        } catch (\Exception $e) {
            self::error('Unable to init DAO: ' . $e->__toString());
        }

    }


    /**
     * get table info
     * @param $table
     * @param $modelInfo
     * @return array
     */
    public function getTableInfo($table, $modelInfo)
    {
        $info = array();
        if ($this->dao instanceof Orm) {
            $info = $this->dao->describe($table);
        }

        if ($this->dao == Pdo::ADAPTOR_DYNAMODB) {
            // read from config and build it...
            /*
            [0] =>
              array(6) {
                'Field' =>
                string(2) "id"
                'Type' =>
                string(16) "int(11) unsigned"
                'Null' =>
                string(2) "NO"
                'Key' =>
                string(3) "PRI"
                'Default' =>
                NULL
                'Extra' =>
                string(14) "auto_increment"
              }

            */
            $fields = Assoc::get($modelInfo, 'fields');
            $keys = Assoc::get($modelInfo, 'keys');
            foreach ($fields as $field => $type) {
                if ($type == 'int' || $type == 'integer') {
                    $type = 'int';
                } else {
                    $type = 'varchar';
                }
                $piece = array(
                    'Field' => $field,
                    'Type' => $type,
                    'Key' => isset($keys[$field]) ? 'PRI' : '',
                    'Null' => isset($keys[$field]) ? 'NO' : 'YES',
                );
                if (!empty($keys[$field])) {
                    $piece['Key_Type'] = $keys[$field];
                }
                $info[] = $piece;
            }
        }
        return $info;

    }


    public function getTablesAndGenerateModels()
    {
        $tables = array();
        $models = Config::get('model');
        foreach ($models as $table => $modelInfo) {
            try {
                self::msg('-- analysing table ' . $table);
                $tableInfo = $this->gettableInfo($table, $modelInfo);
                $this->generateModel($table, $tableInfo, $modelInfo);
            } catch (\Exception $e) {
                self::error('Error analyzing table: ' . $table . ': ' . $e->__toString());
            }
        }
        return $tables;

    }

    public function generateModel($table, $tableInfo, $modelInfo)
    {
        $isDynamo = ($this->dao == Pdo::ADAPTOR_DYNAMODB);
        // figure out path and namespace first
        $path = Config::get('path');
        $ns = Config::get('namespace');
        // table info pattern:
        // 1st, fields and keys
        $PrimaryKey = array();
        $fields = array();
        $sqlFields = array();
        $getters = array();
        $setters = array();
        $className = Config::get('class.'.$table);
        $path .= '/' . $className . '.php';
        foreach ($tableInfo as $field) {
            $fName = Assoc::get($field, 'Field');
            $fKey   = $isDynamo ? $fName : $table . '_' . $fName;
            $fKeyMap = $isDynamo ? $fName : "`{$table}`.`{$fName}`";
            $fType = Assoc::get($field, 'Type');
            $fRequired = Assoc::get($field, 'Null') == 'NO';
            if (Assoc::get($field, 'Key') == 'PRI') {
                $PrimaryKey[] = "'{$fKey}'";
            }
            $phpType = $this->mapMySQLTypeToPHPType($fType);
            $camelName = String::camelCase($fName);
            $fields[$fKey] = $fieldInfo = array(
                'type' => $phpType,
                'required' => $fRequired,
                'field' => $fName,
                'field_key' => $fKey,
                'field_map' => $fKeyMap,
                'getter' => "get{$camelName}",
                'setter' => "set{$camelName}",
            );
            if ($isDynamo && Assoc::get($field, 'Key') == 'PRI') {
                $fields[$fKey]['key_type'] = $fieldInfo['key_type'] = Assoc::get($field, 'Key_Type');
            }
            // build sql fields here:
            $constant = strtoupper('FIELD_' . $fName);
            $sqlFields[$constant] = $fKeyMap;
            $getters[$fName] = $this->composeGetter($camelName, $fieldInfo);
            $setters[$fName] = $this->composeSetter($camelName, $fieldInfo);
        }
        // next, relations
        $relations = Assoc::get($modelInfo, 'relations');
        // deal with relations - add table class from the class name
        $mergedRelations = array();
        if (!empty($relations)) {
            // build relations
            foreach ($relations as $relation) {
                $rTable = Assoc::get($relation, 'table');
                $rClass = Config::get('class.' . $rTable);
                $type = Assoc::get($relation, 'type') == 'S' ? Model::RELATION_SINGLE : Model::RELATION_MULTI;
                $rType = ($type == Model::RELATION_SINGLE) ? 'Model::RELATION_SINGLE' : 'Model::RELATION_MULTI';
                $onTmp = array();
                $rCondition = trim(Assoc::get($relation, 'condition'));
                $on = (array) Assoc::get($relation, 'on');
                $join = Assoc::get($relation, 'join', 'INNER');
                foreach ($on as $left => $right) {
                    $onTmp[] = "`{$table}`.`{$left}` = `{$rTable}`.`{$right}`";
                }
                // add to relation
                $mergedRelations[$rTable] = array(
                    'class' => "{$ns}\\{$rClass}",
                    'type' => "{$rType}",
                    'join' => " {$join} JOIN `{$rTable}` ON " . implode(' AND ', $onTmp) . " ",
                    'condition' => "{$rCondition}",
                );
                if ($type == Model::RELATION_MULTI) {
                    $mergedRelations[$rTable]['setter_multi'] = 'relationSet' . $rClass . 'List';
                    $mergedRelations[$rTable]['setter'] = 'relationAdd' . $rClass;
                    $mergedRelations[$rTable]['getter_multi'] = 'relationGet' . $rClass . 'List';
                    $mergedRelations[$rTable]['getter'] = 'relationGet' . $rClass . 'ById';
                } else {
                    $mergedRelations[$rTable]['setter'] = 'relationSet' . $rClass;
                    $mergedRelations[$rTable]['getter'] = 'relationGet' . $rClass;
                }
                $getters[$rTable] = $this->composeRelationGetter($relation);
                $setters[$rTable] = $this->composeRelationSetter($relation);
            }
        }
        $src = $this->composeClass($table, $className, $ns, $PrimaryKey, $sqlFields, $fields, $setters, $getters, $mergedRelations);
        // write to file
        if (@file_put_contents($path, $src)) {
            self::msg(' - class ' . $className . ' is written to ' . $path);
        } else {
            self::error('Unable to write to file: ' . $path);
        }

    }// end generateModel


    /**
     * @param $table
     * @param $className
     * @param $ns
     * @param $PrimaryKey
     * @param $sqlFields
     * @param $fields
     * @param $setters
     * @param $getters
     * @param $mergedRelations
     * @return string
     */
    protected function composeClass($table, $className, $ns, $PrimaryKey, $sqlFields, $fields, $setters, $getters, $mergedRelations)
    {
        $isDynamo = ($this->dao == Pdo::ADAPTOR_DYNAMODB);
        // compose the class
        // fields
        $tmpAr = array();
        foreach ($fields as $name => $field) {
            $tmp = 'array(';
            foreach ($field as $k => $v) {
                // select fields
                if ($k == 'field_map') {
                    $fieldConst = strtoupper($field['field']);
                    $selectFields[$name] = "    CONST FIELD_MAP_{$fieldConst} = '{$v}';";
                }
                // compose array
                if ($k != 'required') {
                    $v = "'{$v}'";
                } else {
                    $v = ($v) ? 'true' : 'false';
                }
                $tmp .= "
            '{$k}' => {$v},";
            }
            $tmp .= "
        ),";
            $tmpAr[] = "
        '{$name}' => {$tmp}";
        }
        // recompose fields
        $fields = 'array(' . implode('', $tmpAr) . '
    );';
        $selectFields = implode(PHP_EOL, $selectFields);
        // keys
        $PrimaryKey = implode(', ', $PrimaryKey);
        // relations
        $tmpAr = array();
        foreach ($mergedRelations as $key => $relation) {
            $rs = "
        '{$key}' => array(";
            foreach ($relation as $k => $v) {
                if (strpos($v, 'Model::') === false) {
                    $v = "'{$v}'";
                }
                $rs .= "
            '{$k}' => {$v},";
            }
            $rs .= '
        ),';
            $tmpAr[] = $rs;
        }
        $mergedRelations = implode('', $tmpAr);
        $constants = '';
        foreach ($sqlFields as $k => $v) {
            $constants .= "    CONST {$k} = '{$v}';\n";
        }
        $setter = implode('', $setters);
        $getter = implode('', $getters);
        // super class
        $superClass = $isDynamo ? 'Dynamodb' : 'Model';
        $useDynamo = $isDynamo ? 'use CoreORM\\Model\\Dynamodb' : 'use CoreORM\\Model';
        // compose
        return "<?php
/**
 * {$className} model
 * @author ModelGenerator
 */
namespace {$ns};
{$useDynamo};
class {$className} extends {$superClass}
{
$constants
    protected \$table = '{$table}';
    protected \$fields = {$fields}
    protected \$key = array({$PrimaryKey});
    protected \$relations = array({$mergedRelations}
    );
    {$setter}
    {$getter}
}";

    }

    protected function composeRelationGetter($relation)
    {
        $table = Assoc::get($relation, 'table');
        $type = Assoc::get($relation, 'type');
        $class = Config::get('class.' . $table);
        if ($type == 'S') {
            // 1:1 relation, just use single getter
            return "
    /**
     * get related {$class} model
     * @return {$class}
     */
    public function relationGet{$class}()
    {
        if (!empty(\$this->data['_relation_{$class}'])) {
            return \$this->data['_relation_{$class}'];
        }
        return new {$class}();
    }";
        }
        if ($type == 'M') {
            // we will need 2 getters
            return "
    /**
     * get related {$class} model list
     * @return {$class}
     */
    public function relationGet{$class}List()
    {
        if (!empty(\$this->data['_relation_{$class}'])) {
            return (array) \$this->data['_relation_{$class}'];
        }
        return array();
    }
    /**
     * get related {$class} model by id
     * @param \$id
     * @return {$class}
     */
    public function relationGet{$class}ById(\$id)
    {
        return \$this->getOneModel('_relation_{$class}', \$id, '{$class}');
    }";
        }
    }

    protected function composeRelationSetter($relation)
    {
        $table = Assoc::get($relation, 'table');
        $type = Assoc::get($relation, 'type');
        $class = Config::get('class.' . $table);
        if ($type == 'S') {
            return "
    /**
     * set related {$class} model
     * @param {$class} \${$class}
     */
    public function relationSet{$class}({$class} \${$class})
    {
        \$this->data['_relation_{$class}'] = \${$class};
    }";
        }
        if ($type == 'M') {
            // we will need 2 setters
            return "
    /**
     * set related {$class} model
     * @param {$class} \${$class}
     */
    public function relationAdd{$class}({$class} \${$class})
    {
        return \$this->addOneModel(\${$class}, '_relation_{$class}');
    }
    /**
     * set related {$class} list
     * @param \$list
     * @return \$this
     */
    public function relationSet{$class}List(\$list)
    {
        \$this->data['_relation_{$class}'] = \$list;
        return \$this;
    }";
        }

    }

    protected function composeGetter($camelName, $fieldInfo)
    {
        $func = "parent::rawGetFieldData('{$fieldInfo['field_key']}', \$default, \$filter)";
        $extraParam = null;
        $hint = null;
        if ($fieldInfo['type'] == 'datetime') {
            $extraParam = "\$format = 'jS F, Y H:i', ";
            $func = "parent::formatDateByName('{$fieldInfo['field_key']}', \$format, \$default, \$filter)";
            $hint = "\n     * @param string \$format";
        }
        return "
    /**
     * retrieve {$camelName}{$hint}
     * @param mixed \$default
     * @param array \$filter filter call back function
     * @return {$fieldInfo['type']}
     */
    public function get{$camelName}({$extraParam}\$default = null, \$filter = array())
    {
        return {$func};
    }";

    }

    protected function composeSetter($camelName, $fieldInfo)
    {
        return "
    /**
     * set {$camelName}
     * @param mixed \$value
     * @return \$this
     */
    public function set{$camelName}(\$value)
    {
        return parent::rawSetFieldData('{$fieldInfo['field_key']}', \$value);
    }";

    }



    protected function mapMySQLTypeToPHPType($type)
    {
        $type = strtolower($type);
        if (strpos($type, 'int') !== false) {
            return 'int';
        }
        if (strpos($type, 'date') !== false ||
            strpos($type, 'time') !== false) {
            return 'datetime';
        }
        if (strpos($type, 'decimal') !== false ||
            strpos($type, 'float') !== false ||
            strpos($type, 'double') !== false ||
            strpos($type, 'real') !== false) {
            return 'float';
        }
        // default is always string
        return 'string';

    }// end mapMySQLTypeToPHPType


    protected function getFieldInfo($fieldInfo)
    {
        $info = array();
        if (!empty($fieldInfo)) {
            // check type (default to string)
            $type = Assoc::get($fieldInfo, 'Type');

        }
    }


}
