<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;

class HomeContentController extends Controller
{
    public function index()
    {
        $homeContents = HomeContent::orderBy('id', 'desc')->get();
        return view('admin.home_content.index', compact('homeContents'));
    }

    public function create()
    {
        return view('admin.home_content.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_html' => 'required|string',
            'custom_css' => 'nullable|string',
            'image_position' => 'required|in:top,middle,bottom',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image_mime'] = $file->getMimeType();
            $validated['image_blob'] = file_get_contents($file->getRealPath());
        }

        if ($validated['status'] === 'active') {
            HomeContent::where('status', 'active')->update(['status' => 'inactive']);
        }

        HomeContent::create($validated);

        return redirect()->route('admin.home-content.index')->with('success', 'Home content created successfully!');
    }

    public function edit(HomeContent $homeContent)
    {
        return view('admin.home_content.edit', compact('homeContent'));
    }

    public function update(Request $request, HomeContent $homeContent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_html' => 'required|string',
            'custom_css' => 'nullable|string',
            'image_position' => 'required|in:top,middle,bottom',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image_mime'] = $file->getMimeType();
            $validated['image_blob'] = file_get_contents($file->getRealPath());
        }

        if ($validated['status'] === 'active') {
            HomeContent::where('id', '!=', $homeContent->id)->update(['status' => 'inactive']);
        }

        $homeContent->update($validated);

        return redirect()->route('admin.home-content.index')->with('success', 'Home content updated successfully!');
    }

    public function destroy(HomeContent $homeContent)
    {
        $homeContent->delete();
        return redirect()->route('admin.home-content.index')->with('success', 'Home content deleted.');
    }

    /**
     * Efficient binary image retrieval mechanism for frontend serving.
     */
    public function showImage(HomeContent $homeContent)
    {
        if (!$homeContent->image_blob || !$homeContent->image_mime) {
            abort(404);
        }

        return response($homeContent->image_blob)
            ->header('Content-Type', $homeContent->image_mime)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
