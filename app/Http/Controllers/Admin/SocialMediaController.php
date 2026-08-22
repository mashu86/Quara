<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function index()
    {
        $socialMedias = SocialMedia::orderBy('sort_order', 'asc')->get();
        return view('admin.social_media.index', compact('socialMedias'));
    }

    public function create()
    {
        return view('admin.social_media.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:whatsapp,instagram,facebook,youtube',
            'country_code' => 'required_if:type,whatsapp|nullable|string|max:10',
            'phone_number' => 'required_if:type,whatsapp|nullable|string|max:20',
            'url' => 'required_unless:type,whatsapp|nullable|url|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        SocialMedia::create($validated);

        return redirect()->route('admin.social-media.index')->with('success', 'Social media entry added successfully!');
    }

    public function edit(SocialMedia $socialMedia)
    {
        return view('admin.social_media.edit', compact('socialMedia'));
    }

    public function update(Request $request, SocialMedia $socialMedia)
    {
        $validated = $request->validate([
            'type' => 'required|in:whatsapp,instagram,facebook,youtube',
            'country_code' => 'required_if:type,whatsapp|nullable|string|max:10',
            'phone_number' => 'required_if:type,whatsapp|nullable|string|max:20',
            'url' => 'required_unless:type,whatsapp|nullable|url|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $socialMedia->update($validated);

        return redirect()->route('admin.social-media.index')->with('success', 'Social media entry updated!');
    }

    public function destroy(SocialMedia $socialMedia)
    {
        $socialMedia->delete();
        return redirect()->route('admin.social-media.index')->with('success', 'Social media entry deleted.');
    }
}
