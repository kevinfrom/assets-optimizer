<?php

$minPhpVersion = '8.2.0';

$requiredExtensions = [
    'mbstring',
    'gd',
    'fileinfo',
];

if (version_compare(PHP_VERSION, $minPhpVersion, '>=') === false) {
    ini_set('display_errors', true);
    trigger_error("Minimum PHP version of \"$minPhpVersion\" required.", E_USER_ERROR);
}

foreach ($requiredExtensions as $requiredExtension) {
    if (extension_loaded($requiredExtension) === false) {
        ini_set('display_errors', true);
        trigger_error("Required PHP extension \"$requiredExtension\" not loaded.", E_USER_ERROR);
    }
}
