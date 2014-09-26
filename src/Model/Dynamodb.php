<?php
namespace CoreORM\Model;
use CoreORM\Model, CoreORM\Utility\Assoc;

/**
 * Dynamodb model
 * Class Dynamodb
 * @package CoreORM\Model
 */
class Dynamodb extends Model
{
    const READ = 1;
    const DELETE = 2;

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
    public function setJsonData($field, $item, $value)
    {
        // must be valid in the fields definition
        if (isset($this->fields[$field][$item])) {
            // find the data (and it should be json array)
            $data = Assoc::get($this->data, $field);
            $dataArray =  array();
            if (!empty($data)) {
                $dataArray = json_encode($data, true);
            }
            $dataArray[$item] = $value;
            $this->rawSetFieldData($field, json_encode($dataArray));
        }
        return $this;

    }


    /**
     * get condition
     * @param array $opts extra options
     * @return array
     */
    public function queryGetCondition($opts = array(), $type = self::READ)
    {
        $opts = (array) $opts;
        $opts['TableName'] = $this->table();
        if ($type == self::READ) {
            $opts['KeyConditions'] = $this->condition;
        }
        if ($type == self::DELETE) {
            $opts['Key'] = $this->condition;
        }
        return $opts;

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

    /**
     * set query condition for delete
     * @param $field
     * @param $type
     * @param $value
     * @return $this
     */
    public function querySetDeleteCondition($field, $type, $value)
    {
        $this->condition[$field] = array($type => $value);
        return $this;

    }

}
