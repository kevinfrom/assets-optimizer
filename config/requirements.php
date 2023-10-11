<?php

$minPhpVersion = '8.2.0';

$requiredExtensions = [
    'mbstring',
    'gd',
    'fileinfo',
];

ini_set('display_errors', true);

if (version_compare(PHP_VERSION, $minPhpVersion, '>=') === false) {
    trigger_error("Minimum PHP version of \"$minPhpVersion\" required.", E_USER_ERROR);
}

foreach ($requiredExtensions as $requiredExtension) {
    if (extension_loaded($requiredExtension) === false) {
        trigger_error("Required PHP extension \"$requiredExtension\" not loaded.", E_USER_ERROR);
    }
}

$requiredMemoryLimitInMb = 256;
$memoryLimit = parseByteStringAsBytes(ini_get('memory_limit'));

if ($memoryLimit < ($requiredMemoryLimitInMb * 1024 * 1024)) {
    trigger_error("'Minimum PHP \"memory_limit\" of {$requiredMemoryLimitInMb}MB required.", E_USER_ERROR);
}

$requiredMaxPostInMb = 128;
$postMaxSize = parseByteStringAsBytes(ini_get('post_max_size'));

if ($postMaxSize < ($requiredMaxPostInMb * 1024 * 1024)) {
    trigger_error("Minimum PHP \"post_max_size\" limit of {$requiredMaxPostInMb}MB required.", E_USER_ERROR);
}

$requiredUploadMaxFilesizeInMb = 128;
$uploadMaxFilesize = parseByteStringAsBytes(ini_get('upload_max_filesize'));

if ($uploadMaxFilesize < ($requiredUploadMaxFilesizeInMb * 1024 * 1024)) {
    trigger_error("Minimum PHP \"upload_max_filesize\" of {$requiredUploadMaxFilesizeInMb}MB required.", E_USER_ERROR);
}

ini_set('display_errors', false);
