<?php

namespace AssetsOptimizer\Routing;

use AssetsOptimizer\Controller\Controller;

class Router
{
    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_PUT = 'PUT';
    public const HTTP_METHOD_PATCH = 'PATCH';
    public const HTTP_METHOD_DELETE = 'DELETE';

    private static Router $_instance;
    private array $_routesMap = [];

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (empty(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    public function addControllerRoute(string $controllerFqn, string $baseRoute): void
    {
        if (class_exists($controllerFqn) === false) {
            throw new InvalidControllerException($controllerFqn);
        }

        $controller = new $controllerFqn();
        if ($controller instanceof Controller === false) {
            throw new InvalidControllerException($controllerFqn);
        }

        if (str_starts_with($baseRoute, '/') === false) {
            $baseRoute = "/$baseRoute";
        }

        $classMethods = array_map(static fn($i) => mb_strtolower($i), get_class_methods($controller));

        foreach ($classMethods as $method) {
            if ($method === 'initialize') {
                continue;
            }

            $route = $method === 'index' ? $baseRoute : "$baseRoute/$method";
            $this->_routesMap[$route] = [$controller, $method];
        }
    }

    public function requireMethod(string $method): void
    {
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) !== $method) {
            throw new MethodNotAllowedException($method);
        }
    }

    public function requireHeader(string $header): string
    {
        $header = $this->getHeader($header);

        if (empty($header)) {
            throw new BadRequestException("Required HTTP header \"$header\" is missing.");
        }

        return $header;
    }

    public function requireQueryParameter(string $key): string
    {
        $result = $this->getQueryParam($key);

        if (empty($result)) {
            throw new BadRequestException("Required query parameter \"$key\" is missing.");
        }

        return $result;
    }

    public function getHeader(string $key): ?string
    {
        return getallheaders()[$key] ?? null;
    }

    public function getContentType(): null|string
    {
        $matches = [];
        preg_match('/(application\/json|application\/x-www-form-urlencoded|multipart\/form-data)/i', $_SERVER['CONTENT_TYPE'] ?? $this->getHeader('Content-Type'), $matches);

        return $matches[1] ?? null;
    }

    public function requestIsJson(): bool
    {
        return mb_strtolower($this->getContentType()) === 'application/json';
    }

    public function requestIsUrlEncoded(): bool
    {
        return mb_strtolower($this->getContentType()) === 'application/x-www-form-urlencoded';
    }

    public function requestIsMultipartFormData(): bool
    {
        return mb_strtolower($this->getContentType()) === 'multipart/form-data';
    }

    private function getRoute(string $url): null|array
    {
        return $this->_routesMap[$url] ?? null;
    }

    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getQueryParam(string $key): ?string
    {
        return $this->getQueryParams()[$key] ?? null;
    }

    public function getPostData(): array
    {
        if ($this->requestIsJson()) {
            return json_decode(file_get_contents('php://input'), true);
        }

        return $_POST;
    }

    public function getUploadedFiles(): array
    {
        return $_FILES;
    }

    public function getUploadedFile(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function handleRequest(): void
    {
        $path = rtrim(parse_url($_SERVER['REQUEST_URI'])['path'] ?? '', '/');

        if (empty($path) || empty(($route = $this->getRoute($path)))) {
            throw new NotFoundException();
        }

        [$controller, $method] = $route;

        $controller->initialize();
        $controller->{$method}();
    }
}
