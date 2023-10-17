<?php

namespace AssetsOptimizer;

use Exception;

class MissingConfigException extends Exception
{
    public function __construct(string $defaultConfig, string $localConfig)
    {
        parent::__construct("Local config missing. Please copy \"$defaultConfig\" as \"$localConfig\" and adjust as necessary.", 500);
    }
}
