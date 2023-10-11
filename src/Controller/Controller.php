<?php

namespace AssetsOptimizer\Controller;

use AssetsOptimizer\Configuration\Config;
use AssetsOptimizer\Configuration\MissingApiKeysException;
use AssetsOptimizer\Routing\NotFoundException;
use AssetsOptimizer\Routing\Router;
use AssetsOptimizer\Routing\UnauthorizedException;
use JetBrains\PhpStorm\NoReturn;

abstract class Controller
{
    public function initialize(): void
    {
        $allowedApiKeys = Config::getInstance()->getConfig('api_keys');

        if (empty($allowedApiKeys)) {
            throw new MissingApiKeysException();
        }

        $apiKey = Router::getInstance()->requireQueryParameter('key');

        if (in_array($apiKey, $allowedApiKeys) === false) {
            throw new UnauthorizedException();
        }
    }

    #[NoReturn] public function respondWithJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    #[NoReturn] public function respondWithFile(string $filePath): void
    {
        if (file_exists($filePath) === false) {
            throw new NotFoundException();
        }

        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        header('Expires: 0');
        header('ETag: ' . hash_file('md5', $filePath));
        header('Content-Type: ' . mime_content_type($filePath));
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');

        echo file_get_contents($filePath);
        exit;
    }
}
