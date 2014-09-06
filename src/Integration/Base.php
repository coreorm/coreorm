<?php
/**
 * CoreORM/Framework
 * Integration base class
 */
namespace CoreORM\Integration;

use CoreORM\Utility\Config, CoreORM\Utility\Debug;
/**
 * Class Base
 * @package CoreORM\Integration
 */
class Base
{
    /**
     * this keeps any possible errors
     * @var array
     */
    protected $errors = array();

    /**
     * the constructor
     */
    public function __construct()
    {
        // 1st, translate config
        $config = $this->translateConfig();
        Config::setArray($config);
        // 2nd, enable debug if debug is enabled by system
        Debug::debug($this->enableDebug());

    }

    /**
     * translate configuration
     * and set the current config with
     * translated configuration
     * @return array
     */
    protected function translateConfig()
    {
        return array();

    }

    /**
     * should we enable debug?
     * @return bool
     */
    protected function enableDebug()
    {
        return false;
    }

    public function reportErrors()
    {
        if (!empty($this->errors)) {
            echo '<ul><strong>Integration Error</strong><br/>' .
                 implode('<li>', $this->errors) . '</ul>';
        }
    }

}
