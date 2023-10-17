<?php

namespace AssetsOptimizer\Routing;

use Exception;

class BadRequestException extends Exception
{
    protected $code = 400;
}
