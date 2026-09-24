<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeCarouselSetting;
use App\Models\HomeCarouselSlide;
use App\Models\HomePageSection;
use App\Models\HomeTestimonial;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;

class HomeCarouselController extends Controller
{
    public function index()
    {
        $settings = HomeCarouselSetting::current();
        HomePageSection::syncDefaults();
        $sections = HomePageSection::orderBy('sort_order')->get()->keyBy('section_key');
        $slides = $this->slideList();
        $testimonials = HomeTestimonial::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.home_carousel.manager', compact('settings', 'sections', 'slides', 'testimonials'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'carousel_type' => 'required|in:hero,contained',
            'animation' => 'required|in:slide,fade',
            'interval_ms' => 'required|integer|min:1500|max:15000',
            'items_desktop' => 'required|numeric|min:1|max:5',
            'items_tablet' => 'required|numeric|min:1|max:4',
            'items_mobile' => 'required|numeric|min:1|max:2',
            'margin_px' => 'required|integer|min:0|max:60',
            'smart_speed_ms' => 'required|integer|min:100|max:2000',
            'sections' => 'required|array',
        ]);
        foreach (array_keys(HomePageSection::TITLES) as $key) {
            $request->validate([
                "sections.{$key}.sort_order" => 'required|integer|min:1|max:99',
                "sections.{$key}.items_to_show" => 'required|integer|min:1|max:30',
                "sections.{$key}.enabled" => 'nullable|boolean',
            ]);
        }
        $data['enabled'] = $request->boolean('enabled');
        foreach (['loop', 'show_nav', 'show_dots', 'autoplay', 'pause_on_hover', 'center_mode'] as $option) {
            $data[$option] = $request->boolean($option);
        }
        $settings = HomeCarouselSetting::current();
        $settings->update(collect($data)->only([
            'enabled', 'carousel_type', 'animation', 'interval_ms',
            'items_desktop', 'items_tablet', 'items_mobile', 'margin_px', 'smart_speed_ms',
            'loop', 'show_nav', 'show_dots', 'autoplay', 'pause_on_hover', 'center_mode',
        ])->all());
        foreach (HomePageSection::TITLES as $key => $title) {
            $section = $request->input("sections.{$key}", []);
            $enabled = $key === 'hero' ? $data['enabled'] : filter_var($section['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            HomePageSection::updateOrCreate(['section_key' => $key], [
                'enabled' => $enabled,
                'sort_order' => (int) $section['sort_order'],
                'items_to_show' => (int) $section['items_to_show'],
            ]);
        }
        $heroCount = (int) $request->input('sections.hero.items_to_show', 5);
        $settings->update(['visible_count' => $heroCount]);
        return redirect()->route('admin.home-carousel.index')->with('success', 'Carousel settings saved.');
    }

    public function store(Request $request)
    {
        $data = $this->validatedSlide($request, true);
        $this->attachImage($request, $data);
        HomeCarouselSlide::create($data);
        return redirect()->route('admin.home-carousel.index')->with('success', 'Carousel slide added.');
    }

    public function edit(HomeCarouselSlide $slide)
    {
        $settings = HomeCarouselSetting::current();
        HomePageSection::syncDefaults();
        $sections = HomePageSection::orderBy('sort_order')->get()->keyBy('section_key');
        $slides = $this->slideList();
        $testimonials = HomeTestimonial::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.home_carousel.manager', compact('settings', 'sections', 'slides', 'testimonials', 'slide'));
    }

    public function update(Request $request, HomeCarouselSlide $slide)
    {
        $data = $this->validatedSlide($request, false);
        $data['heading_x'] = $data['text_x'];
        $data['heading_y'] = $data['text_y'];
        $this->attachImage($request, $data);
        $slide->update($data);
        return redirect()->route('admin.home-carousel.index')->with('success', 'Carousel slide updated.');
    }

    public function saveDesign(Request $request, HomeCarouselSlide $slide)
    {
        $data = $request->validate([
            'heading' => 'nullable|string|max:255',
            'heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heading_font' => 'required|in:serif,sans,trebuchet,classic,system',
            'heading_size' => 'required|integer|min:12|max:84',
            'heading_x' => 'required|numeric|min:0|max:100',
            'heading_y' => 'required|numeric|min:0|max:100',
            'subheading' => 'nullable|string|max:500',
            'subheading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'subheading_font' => 'required|in:serif,sans,trebuchet,classic,system',
            'subheading_size' => 'required|integer|min:10|max:48',
            'subheading_x' => 'required|numeric|min:0|max:100',
            'subheading_y' => 'required|numeric|min:0|max:100',
            'button_text' => 'nullable|string|max:60',
            'link_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/(?!\/))[^\s]*$/i'],
            'button_x' => 'required|numeric|min:0|max:100',
            'button_y' => 'required|numeric|min:0|max:100',
            'overlay_items' => 'nullable|array|max:20',
            'overlay_items.*.id' => 'required|string|max:40',
            'overlay_items.*.type' => 'required|in:text,link',
            'overlay_items.*.text' => 'required|string|max:500',
            'overlay_items.*.url' => ['nullable', 'required_if:overlay_items.*.type,link', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/(?!\/))[^\s]*$/i'],
            'overlay_items.*.x' => 'required|numeric|min:0|max:100',
            'overlay_items.*.y' => 'required|numeric|min:0|max:100',
            'overlay_items.*.font' => 'required|in:serif,sans,trebuchet,classic,system',
            'overlay_items.*.size' => 'required|integer|min:10|max:84',
            'overlay_items.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'overlay_items.*.background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $slide->update($data);
        return response()->json(['message' => 'Slide design saved.', 'id' => $slide->id]);
    }

    public function toggleStatus(HomeCarouselSlide $slide)
    {
        $slide->status = $slide->status === 'active' ? 'inactive' : 'active';
        $slide->save();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $slide->status,
                'message' => 'Status updated to ' . ucfirst($slide->status),
            ]);
        }

        return redirect()->back()->with('success', 'Status updated.');
    }

    public function builder(Request $request, ?HomeCarouselSlide $slide = null)
    {
        $settings = HomeCarouselSetting::current();
        HomePageSection::syncDefaults();
        $sections = HomePageSection::orderBy('sort_order')->get()->keyBy('section_key');
        $slides = $this->slideList();

        if (!$slide && $slides->isNotEmpty()) {
            $slide = $slides->first();
        }

        return view('admin.home_carousel.builder', compact('settings', 'sections', 'slides', 'slide'));
    }

    public function saveSlideFull(Request $request)
    {
        $data = $request->validate([
            'slide_id' => 'nullable|integer',
            'section_key' => 'required|in:hero,lookbook',
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:60',
            'link_url' => 'nullable|string|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'overlay_items' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:12288',
        ]);

        $slideId = $request->input('slide_id');
        $slide = $slideId ? HomeCarouselSlide::find($slideId) : null;

        $updateData = [
            'section_key' => $data['section_key'],
            'heading' => $data['heading'] ?? '',
            'subheading' => $data['subheading'] ?? '',
            'button_text' => $data['button_text'] ?? '',
            'link_url' => $data['link_url'] ?? '',
            'status' => $data['status'],
            'sort_order' => $data['sort_order'] ?? ($slide ? $slide->sort_order : 1),
            'overlay_items' => $data['overlay_items'] ?? [],
        ];

        if ($request->hasFile('image')) {
            $this->attachImage($request, $updateData);
        }

        if ($slide) {
            $slide->update($updateData);
        } else {
            if (!$request->hasFile('image')) {
                return response()->json(['success' => false, 'message' => 'An image is required for new slides.'], 422);
            }
            $slide = HomeCarouselSlide::create($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slide saved successfully!',
            'slide' => [
                'id' => $slide->id,
                'heading' => $slide->heading,
                'subheading' => $slide->subheading,
                'image_url' => $slide->image_url,
                'status' => $slide->status,
                'overlay_items' => $slide->overlay_items,
            ],
            'slides' => $this->slideList()->map(fn($s) => [
                'id' => $s->id,
                'heading' => $s->heading ?: 'Slide #' . $s->id,
                'image_url' => $s->image_url,
                'status' => $s->status,
            ]),
        ]);
    }

    public function destroy(HomeCarouselSlide $slide)
    {
        $slide->delete();
        return redirect()->route('admin.home-carousel.index')->with('success', 'Carousel slide deleted.');
    }

    public function storeTestimonial(Request $request)
    {
        HomeTestimonial::create($request->validate([
            'customer_name' => 'required|string|max:120',
            'review' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'required|integer|min:0|max:9999',
            'status' => 'required|in:active,inactive',
        ]));
        return redirect()->route('admin.home-carousel.index')->with('success', 'Review added.');
    }

    public function destroyTestimonial(HomeTestimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->route('admin.home-carousel.index')->with('success', 'Review deleted.');
    }

    public function showImage(HomeCarouselSlide $slide)
    {
        abort_unless($slide->image_blob && $slide->image_mime, 404);
        return response($slide->image_blob)->header('Content-Type', $slide->image_mime)->header('Cache-Control', 'public, max-age=86400');
    }

    private function validatedSlide(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'heading' => 'nullable|string|max:255',
            'section_key' => 'required|in:hero,lookbook',
            'heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heading_font' => 'required|in:serif,sans,trebuchet,classic,system',
            'heading_size' => 'required|integer|min:12|max:72',
            'subheading' => 'nullable|string|max:500',
            'subheading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'subheading_font' => 'required|in:serif,sans,trebuchet,classic,system',
            'subheading_size' => 'required|integer|min:10|max:40',
            'button_text' => 'nullable|string|max:60',
            'link_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/(?!\/))[^\s]*$/i'],
            'text_x' => 'required|numeric|min:0|max:95',
            'text_y' => 'required|numeric|min:0|max:90',
            'sort_order' => 'required|integer|min:0|max:9999',
            'status' => 'required|in:active,inactive',
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:12288'],
        ]);
    }

    private function attachImage(Request $request, array &$data): void
    {
        if (!$request->hasFile('image')) return;
        $file = $request->file('image');
        $optimized = ImageOptimizerService::optimizeBinary($file, 1920, 1080, 85);
        $data['image_mime'] = $optimized !== null && function_exists('imagewebp') ? 'image/webp' : ($file->getMimeType() ?: 'image/jpeg');
        $data['image_blob'] = $optimized ?? file_get_contents($file->getRealPath());
    }

    private function slideList()
    {
        return HomeCarouselSlide::select([
            'id', 'heading', 'heading_color', 'heading_font', 'subheading', 'subheading_color',
            'section_key', 'image_mime', 'sort_order', 'status', 'created_at', 'updated_at',
            'heading_size', 'subheading_font', 'subheading_size', 'button_text', 'link_url', 'text_x', 'text_y',
            'heading_x', 'heading_y', 'subheading_x', 'subheading_y', 'button_x', 'button_y', 'overlay_items',
        ])->orderBy('sort_order')->orderBy('id')->get();
    }
}
