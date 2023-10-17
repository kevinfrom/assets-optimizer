<?php

namespace AssetsOptimizer\Routing;

class MethodNotAllowedException extends BadRequestException
{
    public function __construct(string $method)
    {
        parent::__construct("$method required.", 400);
    }
}
