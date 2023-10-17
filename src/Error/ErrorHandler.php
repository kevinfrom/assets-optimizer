<?php

namespace AssetsOptimizer\Error;

use AssetsOptimizer\Configuration\Config;
use Error;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Sentry;

class ErrorHandler
{
    #[NoReturn] public function handleError(Error|Exception $e): void
    {
        $isDebug = Config::getInstance()->getConfig('debug') ?? false;

        if ($e->getCode() >= 500 || empty($e->getCode())) {
            Sentry\captureException($e);
        }

        if ($isDebug) {
            http_response_code(500);
            dd($e);
        }

        http_response_code($e->getCode() ?: 500);
        echo $e->getMessage();
        exit;
    }
}
