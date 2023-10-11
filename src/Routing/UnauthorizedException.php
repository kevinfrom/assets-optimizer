<?php

namespace AssetsOptimizer\Routing;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Unauthorized', 401);
    }
}
