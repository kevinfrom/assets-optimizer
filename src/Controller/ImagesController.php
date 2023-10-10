<?php

namespace AssetsOptimizer\Controller;

use AssetsOptimizer\Routing\BadRequestException;
use AssetsOptimizer\Routing\ImageConvertException;
use AssetsOptimizer\Routing\ImageResizeException;
use AssetsOptimizer\Routing\NotFoundException;
use AssetsOptimizer\Routing\Router;
use AssetsOptimizer\Routing\UploadException;
use GdImage;
use JetBrains\PhpStorm\NoReturn;

class ImagesController extends Controller
{

    #[NoReturn] public function get(): void
    {
        Router::getInstance()->requireMethod(Router::HTTP_METHOD_GET);

        $file = Router::getInstance()->requireQueryParameter('file');
        $format = mb_strtolower(Router::getInstance()->requireQueryParameter('f'));
        $width = Router::getInstance()->getQueryParam('w');
        $height = Router::getInstance()->getQueryParam('h');
        $quality = Router::getInstance()->getQueryParam('q') ?: 100;

        $filePath = UPLOADS_DIR . DS . $file;

        if (file_exists($filePath) === false) {
            throw new NotFoundException();
        }

        if (file_exists(OPTIMIZED_DIR) === false && is_dir(OPTIMIZED_DIR) === false) {
            mkdir(OPTIMIZED_DIR, 0770);
        }

        $format = $this->prepareNewFormat($format);

        if ($width) {
            $this->respondWithFile($this->resizeImage($filePath, $format, $quality, $width, $height));
        }

        $this->respondWithFile($this->convertImageFormat($filePath, $format, $quality));
    }

    private function prepareNewFormat(string $newFormat): string
    {
        $acceptHeader = mb_strtolower(Router::getInstance()->getHeader('Accept'));

        if ($newFormat === 'auto') {
            $newFormat = 'avif';
        }

        if ($newFormat === 'avif') {
            if ($acceptHeader !== '*/*' && str_contains($acceptHeader, 'image/avif') === false) {
                $newFormat = 'webp';
            }
        }

        if ($newFormat === 'webp') {
            if ($acceptHeader !== '*/*' && str_contains($acceptHeader, 'image/webp') === false) {
                $newFormat = 'jpeg';
            }
        }

        return $newFormat;
    }

    private function prepareGdImageFromFile(string $filePath): GdImage
    {
        $fileExtension = pathinfo($filePath)['extension'];

        if ($fileExtension === 'jpg' || $fileExtension === 'jpeg') {
            $image = imagecreatefromjpeg($filePath);
        } elseif ($fileExtension === 'png') {
            $image = imagecreatefrompng($filePath);
        } elseif ($fileExtension === 'webp') {
            $image = imagecreatefromwebp($filePath);
        } elseif ($fileExtension === 'avif') {
            $image = imagecreatefromavif($filePath);
        }

        if ($image instanceof GdImage === false) {
            throw new ImageConvertException($filePath);
        }

        return $image;
    }

    private function saveGdImageToFile(GdImage $image, string $outputPath, string $newFormat, int $quality = 100): void
    {
        $convertSuccess = false;

        if ($newFormat === 'jpg' || $newFormat === 'jpeg') {
            $convertSuccess = imagejpeg($image, $outputPath, $quality);
        } elseif ($newFormat === 'png') {
            $convertSuccess = imagepng($image, $outputPath);
        } elseif ($newFormat === 'webp') {
            $convertSuccess = imagewebp($image, $outputPath, $quality);
        } elseif ($newFormat === 'avif') {
            $convertSuccess = imageavif($image, $outputPath, $quality);
        }

        if ($convertSuccess === false) {
            throw new ImageResizeException("Could not save GdImage to file $outputPath");
        }
    }

    private function resizeImage(string $filePath, string $newFormat, int $quality, int $width, null|int|string $height): string
    {
        $image = $this->prepareGdImageFromFile($filePath);

        if ($width < 1) {
            throw new ImageResizeException('Width must be at least 1');
        }

        $resizedImage = imagescale($image, $width, $height ?: -1);

        if ($resizedImage instanceof GdImage === false) {
            throw new ImageResizeException("Could not resize image $filePath");
        }

        [$originalWidth, $originalHeight] = getimagesize($filePath);
        $width = min($width, $originalWidth);
        $height = $height ? min($height, $originalHeight) : -1;

        $fileName = pathinfo($filePath)['filename'];
        $fileNameExtra = "_$width" . ($height > 0 ? "x$height" : '');
        $imageOutputPath = OPTIMIZED_DIR . DS . "$fileName$fileNameExtra.$newFormat";

        if (file_exists($imageOutputPath)) {
            return $imageOutputPath;
        }

        $image = $this->prepareGdImageFromFile($filePath);
        $image = imagescale($image, $width, $height);
        $this->saveGdImageToFile($image, $imageOutputPath, $newFormat, $quality);
        imagedestroy($image);

        return $imageOutputPath;
    }

    private function convertImageFormat(string $filePath, string $newFormat, int $quality = 100): string
    {
        if (file_exists($filePath) === false) {
            throw new NotFoundException();
        }

        if ($quality > 100) {
            $quality = 100;
        }

        $fileName = pathinfo($filePath)['filename'];

        $imageOutputPath = OPTIMIZED_DIR . DS . "$fileName.$newFormat";

        if (file_exists($imageOutputPath)) {
            return $imageOutputPath;
        }

        $image = $this->prepareGdImageFromFile($filePath);

        $this->saveGdImageToFile($image, $imageOutputPath, $newFormat, $quality);
        imagedestroy($image);

        return $imageOutputPath;
    }

    #[NoReturn] public function upload(): void
    {
        Router::getInstance()->requireMethod(Router::HTTP_METHOD_POST);

        $image = Router::getInstance()->getUploadedFile('image');

        if (empty($image['size']) || empty($image['error']) === false) {
            throw new BadRequestException('"image" missing in POST body');
        }

        $uploadPath = $this->prepareUploadPath($image['name']);

        if (move_uploaded_file($image['tmp_name'], $uploadPath) === false) {
            throw new UploadException("Failed to upload file to $uploadPath");
        }

        $this->respondWithJson(['status' => 'ok']);
    }

    private function prepareUploadPath(string $fileName): string
    {
        $allowedFileExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'avif',
        ];

        $fileExtension = mb_strtolower(ltrim(substr($fileName, -4), '.'));

        if (in_array($fileExtension, $allowedFileExtensions) === false) {
            throw new BadRequestException("Filetype \"$fileExtension\" not allowed.");
        }

        if (file_exists(UPLOADS_DIR) === false || is_dir(UPLOADS_DIR) === false) {
            mkdir(UPLOADS_DIR, 0770);
        }

        $name = str_replace(".$fileExtension", '', $fileName);

        $characterConversionMap = [
            'æ' => 'ae',
            'ø' => 'oe',
            'ö' => 'oe',
            'å' => 'aa',
            'ä' => 'aa',
            'ü' => 'ue',
            'ß' => 'ss',
            ' ' => '-',
        ];

        $name = str_replace(array_keys($characterConversionMap), $characterConversionMap, $name);
        $name = preg_replace('/(-)+/', '-', $name);

        $resultPath = UPLOADS_DIR . DS . "$name.$fileExtension";

        $i = 0;
        while (file_exists($resultPath)) {
            $i++;
            $resultPath = UPLOADS_DIR . DS . "$name-$i.$fileExtension";
        }

        return $resultPath;
    }
}
