<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class StorageFileController extends Controller
{
    public function show(string $path)
    {
        // Sanitize path to prevent directory traversal
        $cleanPath = str_replace('\\', '/', $path);
        $segments = array_values(array_filter(explode('/', $cleanPath), static fn ($segment) => $segment !== '' && $segment !== '.' && $segment !== '..'));
        $cleanPath = implode('/', $segments);

        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }
        if (str_starts_with($cleanPath, 'media/')) {
            $cleanPath = substr($cleanPath, 6);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($cleanPath)) {
            $publicPath = public_path($cleanPath);
            if (file_exists($publicPath) && is_file($publicPath)) {
                return Response::file($publicPath, ['Cache-Control' => 'public, max-age=31536000']);
            }

            $defaultLogo = Setting::logoPath();
            if (file_exists($defaultLogo)) {
                return Response::file($defaultLogo, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
            abort(404);
        }

        $fullPath = $disk->path($cleanPath);
        $mimeType = $disk->mimeType($cleanPath) ?: 'application/octet-stream';

        return Response::file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
