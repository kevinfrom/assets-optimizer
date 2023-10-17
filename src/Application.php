<?php

namespace AssetsOptimizer;

use AssetsOptimizer\Configuration\Config;
use AssetsOptimizer\Controller\ImagesController;
use AssetsOptimizer\Routing\Router;
use Sentry;

class Application
{

    public function __construct()
    {
        $configPath = dirname(__DIR__) . DS . 'config' . DS . 'app.php';
        $localConfigPath = dirname(__DIR__) . DS . 'config' . DS . 'app.local.php';

        if (file_exists($configPath) === false || file_exists($localConfigPath) === false) {
            throw new MissingConfigException($configPath, $localConfigPath);
        }

        Config::getInstance()->setConfig(array_merge(
            require_once $configPath,
            require_once $localConfigPath
        ));

        Sentry\init([
            'dsn' => 'https://0546d80e8babfd80b63d14ce8c36de40@o4505521775706112.ingest.sentry.io/4506065174134784',
            'environment' => Config::getInstance()->getConfig('debug') ? 'development' : 'production',
        ]);

        Router::getInstance()->addControllerRoute(ImagesController::class, '/images');
    }

    public function process(): void
    {
        Router::getInstance()->handleRequest();
    }
}
