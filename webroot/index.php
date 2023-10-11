<?php
/**
 * All requests must go through this file to bootstrap the application correctly.
 */

const DS = DIRECTORY_SEPARATOR;

require_once dirname(__DIR__) . DS . 'library' . DS . 'paths.php';
require_once LIBRARY_DIR . DS . 'functions.php';
require_once CONFIG_DIR . DS . 'requirements.php';
require_once VENDOR_DIR . DS . 'autoload.php';

use AssetsOptimizer\Application;
use AssetsOptimizer\Error\ErrorHandler;

try {
    $app = new Application();
    $app->process();
} catch (\Throwable $e) {
    $errorHandler = new ErrorHandler();
    $errorHandler->handleError($e);
}
