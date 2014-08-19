<?php
/**
 * Dao Exception
 */
namespace CoreORM\Exception;

use CoreORM\Adaptor\Pdo;

class Dao extends \Exception
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var Pdo
     */
    public $adaptor;

}