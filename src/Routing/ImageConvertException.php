<?php

namespace AssetsOptimizer\Routing;

use Exception;

class ImageConvertException extends Exception
{
    public function __construct(string $fileName)
    {
        parent::__construct("Failed to convert image $fileName", 500);
    }
}
