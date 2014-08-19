<?php
/**
 * Dao Exception
 */
namespace CoreORM\Exception;

class Model extends \Exception
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var string
     */
    public $class;

}