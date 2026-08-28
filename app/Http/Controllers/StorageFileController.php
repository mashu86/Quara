<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Response;

class StorageFileController extends Controller
{
    public function show(string $path)
    {
        // Sanitize path to prevent directory traversal
        $cleanPath = str_replace(['..', '\\'], ['', '/'], $path);
        $cleanPath = ltrim($cleanPath, '/');

        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $fullPath = storage_path('app/public/'.$cleanPath);

        if (! file_exists($fullPath) || ! is_file($fullPath)) {
            $publicPath = public_path($cleanPath);
            if (file_exists($publicPath) && is_file($publicPath)) {
                return Response::file($publicPath);
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

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return Response::file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
