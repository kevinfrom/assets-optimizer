<?php

namespace AssetsOptimizer;

use AssetsOptimizer\Configuration\Config;
use AssetsOptimizer\Controller\ImagesController;
use AssetsOptimizer\Routing\Router;

class Application
{

    public function __construct()
    {
        $configPath = dirname(__DIR__) . DS . 'config' . DS . 'app.php';
        $localConfigPath = dirname(__DIR__) . DS . 'config' . DS . 'app.local.php';

        if (file_exists($configPath) === false || file_exists($localConfigPath) === false) {
            throw new MissingConfigException();
        }

        Config::getInstance()->setConfig(array_merge(
            require_once $configPath,
            require_once $localConfigPath
        ));

        Router::getInstance()->addControllerRoute(ImagesController::class, '/images');
    }

    public function process(): void
    {
        Router::getInstance()->handleRequest();
    }
}
