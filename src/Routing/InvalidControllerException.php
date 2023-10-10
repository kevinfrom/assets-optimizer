<?php

namespace AssetsOptimizer\Routing;

use Exception;

class InvalidControllerException extends Exception
{
    public function __construct(string $controller)
    {
        parent::__construct("Controller $controller is not a valid Controller", 500);
    }
}
