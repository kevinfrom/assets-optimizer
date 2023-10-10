<?php

namespace AssetsOptimizer\Routing;

use Exception;

class BadRequestException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message, 400);
    }
}
