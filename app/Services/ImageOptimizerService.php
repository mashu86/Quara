<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Optimize an uploaded image file (resize & compress) and store it on disk.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $directory Storage directory (e.g. 'products', 'categories')
     * @param string $disk Storage disk (default 'public')
     * @param int $maxWidth Max width in pixels (default 1600)
     * @param int $maxHeight Max height in pixels (default 1600)
     * @param int $quality Compression quality 1-100 (default 85)
     * @return string Stored relative path (e.g. 'products/abc123xyz.webp' or fallback)
     */
    public static function optimizeAndStore(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 85
    ): string {
        // If not an image or GD not enabled, fallback to default store
        if (!static::isImage($file) || !function_exists('imagecreatefromstring')) {
            return $file->store($directory, $disk);
        }

        try {
            $binary = static::optimizeBinary($file, $maxWidth, $maxHeight, $quality);

            if ($binary === null) {
                return $file->store($directory, $disk);
            }

            // Save as webp if imagewebp is available, else original extension
            $extension = function_exists('imagewebp') ? 'webp' : strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = Str::random(40) . '.' . $extension;
            $path = trim($directory, '/') . '/' . $filename;

            Storage::disk($disk)->put($path, $binary);

            return $path;
        } catch (\Throwable $e) {
            // Safety fallback to normal upload if optimization encounters any issue
            return $file->store($directory, $disk);
        }
    }

    /**
     * Optimize image from UploadedFile or file path and return optimized binary content.
     */
    public static function optimizeBinary(
        UploadedFile|string $fileOrPath,
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 85
    ): ?string {
        $realPath = $fileOrPath instanceof UploadedFile ? $fileOrPath->getRealPath() : $fileOrPath;

        if (!$realPath || !file_exists($realPath)) {
            return null;
        }

        $content = @file_get_contents($realPath);
        if (!$content) {
            return null;
        }

        $sourceImage = @imagecreatefromstring($content);
        if (!$sourceImage) {
            return null;
        }

        // Auto-fix EXIF rotation for mobile camera photos
        $sourceImage = static::correctExifOrientation($realPath, $sourceImage);

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($sourceImage);
            return null;
        }

        // Calculate target dimensions maintaining aspect ratio
        $targetWidth = $width;
        $targetHeight = $height;

        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $targetWidth = max(1, (int) round($width * $ratio));
            $targetHeight = max(1, (int) round($height * $ratio));
        }

        // Resample image
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preserve transparency for PNG / WebP
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $canvas,
            $sourceImage,
            0, 0, 0, 0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        imagedestroy($sourceImage);

        // Capture compressed binary output
        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($canvas, null, $quality);
        } else {
            imagejpeg($canvas, null, $quality);
        }
        $binaryOutput = ob_get_clean();

        imagedestroy($canvas);

        return $binaryOutput ?: null;
    }

    /**
     * Check if uploaded file is a valid image MIME type.
     */
    protected static function isImage(UploadedFile $file): bool
    {
        $mime = strtolower($file->getMimeType() ?? '');
        return str_starts_with($mime, 'image/') && !str_contains($mime, 'svg');
    }

    /**
     * Correct EXIF orientation for mobile camera photos.
     */
    protected static function correctExifOrientation(string $filePath, \GdImage $image): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($filePath);
            if (empty($exif['Orientation'])) {
                return $image;
            }

            switch ($exif['Orientation']) {
                case 3:
                    $rotated = imagerotate($image, 180, 0);
                    if ($rotated !== false) {
                        imagedestroy($image);
                        return $rotated;
                    }
                    break;
                case 6:
                    $rotated = imagerotate($image, -90, 0);
                    if ($rotated !== false) {
                        imagedestroy($image);
                        return $rotated;
                    }
                    break;
                case 8:
                    $rotated = imagerotate($image, 90, 0);
                    if ($rotated !== false) {
                        imagedestroy($image);
                        return $rotated;
                    }
                    break;
            }
        } catch (\Throwable $e) {
            // Ignore EXIF read errors
        }

        return $image;
    }
}
