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

$requiredMemoryLimitInBytes = 256 * 1024 * 1024;
$memoryLimit = parseByteStringAsBytes(ini_get('memory_limit'));

if ($memoryLimit < $requiredMemoryLimitInBytes) {
    trigger_error('Minimum PHP \"memory_limit\" of 256MB required.', E_USER_ERROR);
}

$requiredMaxPostInBytes = 128 * 1024 * 1024;
$postMaxSize = parseByteStringAsBytes(ini_get('post_max_size'));

if ($postMaxSize < $requiredMaxPostInBytes) {
    trigger_error('Minimum PHP "post_max_size" limit of 128MB required.', E_USER_ERROR);
}

$requiredUploadMaxFilesizeInBytes = 128 * 1024 * 1024;
$uploadMaxFilesize = parseByteStringAsBytes(ini_get('upload_max_filesize'));

if ($uploadMaxFilesize < $requiredUploadMaxFilesizeInBytes) {
    trigger_error('Minimum PHP "upload_max_filesize" of 128MB required.', E_USER_ERROR);
}

ini_set('display_errors', false);
