<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryVideoController extends Controller
{
    /**
     * Stream a category video from private storage. Reached only through a
     * short-lived signed URL, so the file has no permanent public address.
     */
    public function show(Category $category)
    {
        $path = $category->video_path;
        abort_if(!$path || str_starts_with($path, 'storage/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
