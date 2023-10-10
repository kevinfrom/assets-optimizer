<?php

namespace AssetsOptimizer\Controller;

use AssetsOptimizer\Routing\NotFoundException;
use JetBrains\PhpStorm\NoReturn;

abstract class Controller
{
    public function initialize(): void
    {
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
