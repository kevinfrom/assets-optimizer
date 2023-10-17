<?php

namespace AssetsOptimizer\Configuration;

use Exception;

class MissingApiKeysException extends Exception
{
    protected $code = 500;
}
