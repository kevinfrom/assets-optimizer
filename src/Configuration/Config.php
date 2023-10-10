<?php

namespace AssetsOptimizer\Configuration;

class Config
{
    private static Config $_instance;
    private array $_config;

    private function __construct()
    {
    }

    public static function getInstance(): Config
    {
        if (empty(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    public function setConfig(array $config)
    {
        $this->_config = $config;
    }

    public function getConfig(string $key = null)
    {
        if ($key) {
            return extractKeyRecursively($this->_config, $key);
        }

        return $this->_config;
    }
}
