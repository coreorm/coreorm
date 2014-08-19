<?php
/**
 * adaptor
 * exception
 *
 */
namespace CoreORM\Exception;
class Adaptor extends \Exception
{
    public $sql;

    public $bind;

}
?>