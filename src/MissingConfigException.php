<?php

namespace AssetsOptimizer;

use Exception;

class MissingConfigException extends Exception
{
    public function __construct()
    {
        parent::__construct('Local config missing', 500);
    }
}
