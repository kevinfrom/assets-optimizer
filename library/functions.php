<?php

use JetBrains\PhpStorm\NoReturn;

function debug(mixed $data): void
{
    echo '<pre>' . var_export($data, true) . '</pre>';
}

#[NoReturn] function dd(mixed $data): void
{
    debug($data);
    die;
}

function extractKeyRecursively(array $array, string $key, mixed $default = null): mixed
{
    $result = $array;

    foreach (explode('.', $key) as $recursiveKey) {
        if (isset($result[$recursiveKey])) {
            $result = $result[$recursiveKey];
        } else {
            $result = $default;
            break;
        }
    }

    return $result;
}
