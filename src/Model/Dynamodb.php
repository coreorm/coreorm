<?php
namespace CoreORM\Model;
use \Aws\DynamoDb\Enum\ComparisonOperator;
use Aws\DynamoDb\Enum\AttributeAction;
use Aws\DynamoDb\Enum\Type;
use CoreORM\Model, CoreORM\Utility\Assoc;

/**
 * Dynamodb model
 * Class Dynamodb
 * @package CoreORM\Model
 */
class Dynamodb extends Model
{
    const READ = 1;
    const SCAN = 2;
    const DELETE = 3;
    const UPDATE = 4;

    /**
     * query condition
     * @var array
     */
    protected $condition = array();

    /**
     * set json data object
     * @param $field
     * @param $item
     * @param $value
     * @return $this
     * @throws \CoreORM\Exception\Model
     */
    public function rawSetJsonData($field, $item, $value)
    {
        // must be valid in the fields definition
        if (isset($this->fields[$field])) {
            // find the data (and it should be json array)
            $data = Assoc::get($this->data, $field);
            $dataArray = array();
            if (!empty($data)) {
                $dataArray = json_decode($data, true);
            }
            $dataArray[$item] = $value;
            $this->rawSetFieldData($field, json_encode($dataArray));
        }
        return $this;

    }


    /**
     * get condition
     * @param array $opts
     * @param int $type
     * @return array
     */
    public function queryGetCondition($opts = array(), $type = self::READ)
    {
        $opts = (array) $opts;
        $opts['TableName'] = $this->table();
        $qType = $type;
        switch ($type) {
            case self::READ:
            case self::SCAN:
                if (empty($this->condition)) {
                    // compose by field
                    foreach ($this->key as $field) {
                        $type = $this->dynamoDbFieldType($field);
                        $value = $this->rawGetFieldData($field, false);
                        if ($value !== false) {
                            $this->querySetCondition($field, ComparisonOperator::EQ, $type, $value);
                        }
                    }
                }
                $key = ($qType == self::READ) ? 'KeyConditions' : 'ScanFilter';
                $opts[$key] = $this->condition;
                break;
            case self::UPDATE:
                foreach ($this->data as $field => $value) {
                    if (!in_array($field, $this->key)) {
                        $type = $this->dynamoDbFieldType($field);
                        $opts['AttributeUpdates'][$field] = array(
                            'Value' => array($type => $value)
                        );
                    }
                }
            case self::DELETE:
                $opts['Key'] = array();
                // retrieve from all data inside the array
                foreach ($this->key as $field) {
                    $type = $this->dynamoDbFieldType($field);
                    $opts['Key'][$field] = array(
                        $type => $this->rawGetFieldData($field)
                    );
                }
                break;
        }
        return $opts;

    }

    protected function dynamoDbFieldType($field)
    {
        $type = Assoc::get($this->fields, $field . '.type');
        if ($type == 'int' || $type == 'integer') {
            return Type::NUMBER;
        }
        if ($type == 'b' || $type == 'binary') {
            return Type::BINARY;
        }
        // default is string
        return Type::S;

    }

    /**
     * set query condition
     * @param $field
     * @param $operator
     * @param $type
     * @param $value
     * @return $this
     */
    public function querySetCondition($field, $operator, $type, $value)
    {
        $this->condition[$field] = array(
            "ComparisonOperator" => $operator,
            "AttributeValueList" => array(
                array($type => $value)
            )
        );
        return $this;

    }

}
