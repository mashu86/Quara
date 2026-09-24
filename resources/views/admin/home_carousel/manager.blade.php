@extends('layouts.admin')
@section('title', 'Homepage Carousel Master - ' . $siteName . ' Admin')
@section('content')
@php
    $editing = isset($slide);
    $currentSlideSection = old('section_key', $editing ? $slide->section_key : 'hero');
    $previewSlides = $slides->where('section_key', $currentSlideSection)->where('status', 'active');
    $previewSlide = $editing ? $slide : $previewSlides->first();
    $fontOptions = ['serif' => 'Georgia Serif', 'sans' => 'Arial Sans', 'trebuchet' => 'Trebuchet', 'classic' => 'Times New Roman', 'system' => 'System Default'];
    $fontCss = ['serif' => 'Georgia, serif', 'sans' => 'Arial, sans-serif', 'trebuchet' => 'Trebuchet MS, sans-serif', 'classic' => 'Times New Roman, serif', 'system' => 'system-ui, sans-serif'];
    $previewSlidesJson = $slides->where('status', 'active')->groupBy('section_key')->map(fn($group) => $group->map(fn($item) => ['image' => $item->image_url, 'heading' => $item->heading, 'subheading' => $item->subheading, 'heading_color' => $item->heading_color, 'subheading_color' => $item->subheading_color, 'font' => $item->heading_font])->values());
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div><h3 class="fw-bold mb-1">Homepage Carousel Master</h3><p class="text-muted small mb-0">Manage the homepage sections and their carousel content.</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.home-carousel.builder') }}" class="btn btn-danger rounded-pill fw-bold shadow-sm px-4"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Launch Carousel Builder</a>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-dark rounded-pill"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Homepage</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.home-carousel.settings') }}" class="card border-0 rounded-4 shadow-sm mb-4">
    @csrf
    <div class="card-body p-3 p-lg-4">
        <div class="row g-4">
            <div class="col-xl-7">
                <h5 class="fw-bold mb-1">Section order and visibility</h5>
                <p class="small text-muted">Categories, products, offers, lookbook, reviews etc. will be displayed as card carousels after the hero banner.</p>
                <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Section</th><th>Show</th><th>Order</th><th>Items</th></tr></thead><tbody>
                    @foreach(\App\Models\HomePageSection::TITLES as $key => $label)
                        @php($section = $sections->get($key))
                        <tr>
                            <td class="fw-semibold">{{ $label }} @if($key === 'hero')<span class="badge bg-warning text-dark">Recommended</span>@endif</td>
                            <td>@if($key === 'hero')<input type="checkbox" class="form-check-input" name="enabled" value="1" id="heroEnabled" {{ old('enabled', $settings->enabled) ? 'checked' : '' }}>@else<input type="checkbox" class="form-check-input" name="sections[{{ $key }}][enabled]" value="1" {{ old("sections.$key.enabled", $section?->enabled) ? 'checked' : '' }}>@endif</td>
                            <td><input type="number" class="form-control form-control-sm" name="sections[{{ $key }}][sort_order]" min="1" max="99" value="{{ old("sections.$key.sort_order", $section?->sort_order ?? 1) }}" required></td>
                            <td><input type="number" class="form-control form-control-sm" name="sections[{{ $key }}][items_to_show]" min="1" max="30" value="{{ old("sections.$key.items_to_show", $section?->items_to_show ?? 8) }}" required></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                <div class="row g-3 mt-2">
                    <div class="col-sm-4"><label class="form-label fw-semibold">Hero layout</label><select name="carousel_type" id="carouselType" class="form-select"><option value="hero" @selected($settings->carousel_type !== 'contained')>Centered Hero Banner</option><option value="contained" @selected($settings->carousel_type === 'contained')>Rounded Banner Card</option></select></div>
                    <div class="col-sm-4"><label class="form-label fw-semibold">Hero animation</label><select name="animation" id="carouselAnimation" class="form-select"><option value="slide" @selected($settings->animation === 'slide')>Slide</option><option value="fade" @selected($settings->animation === 'fade')>Fade</option></select></div>
                    <div class="col-sm-4"><label class="form-label fw-semibold">Autoplay (ms)</label><input type="number" name="interval_ms" id="carouselInterval" class="form-control" min="1500" max="15000" step="500" value="{{ $settings->interval_ms }}" required></div>
                </div>
                <h6 class="fw-bold mt-4 mb-2">Carousel behavior settings</h6>
                <div class="row g-3">
                    <div class="col-6 col-md-3"><label class="form-label small">Items desktop</label><input class="form-control" type="number" name="items_desktop" min="1" max="5" step="0.1" value="{{ old('items_desktop', $settings->items_desktop ?? 1.5) }}" required></div>
                    <div class="col-6 col-md-3"><label class="form-label small">Items tablet</label><input class="form-control" type="number" name="items_tablet" min="1" max="4" step="0.1" value="{{ old('items_tablet', $settings->items_tablet ?? 1.2) }}" required></div>
                    <div class="col-6 col-md-3"><label class="form-label small">Items mobile</label><input class="form-control" type="number" name="items_mobile" min="1" max="2" step="0.05" value="{{ old('items_mobile', $settings->items_mobile ?? 1.05) }}" required></div>
                    <div class="col-6 col-md-3"><label class="form-label small">Card gap (px)</label><input class="form-control" type="number" name="margin_px" min="0" max="60" step="1" value="{{ old('margin_px', $settings->margin_px ?? 14) }}" required></div>
                    <div class="col-6 col-md-3"><label class="form-label small">Slide speed (ms)</label><input class="form-control" type="number" name="smart_speed_ms" min="100" max="2000" step="50" value="{{ old('smart_speed_ms', $settings->smart_speed_ms ?? 450) }}" required></div>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-3">
                    @foreach(['loop'=>'Loop slides','center_mode'=>'Center active slide','show_nav'=>'Show arrows','show_dots'=>'Show dots','autoplay'=>'Autoplay','pause_on_hover'=>'Pause on hover'] as $option => $label)
                        <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="{{ $option }}" value="1" @checked(old($option, $settings->$option ?? in_array($option, ['loop','show_nav','show_dots','autoplay','pause_on_hover'], true)))><span class="form-check-label small">{{ $label }}</span></label>
                    @endforeach
                </div>
                @foreach($errors->all() as $error)<div class="text-danger small mt-2">{{ $error }}</div>@endforeach
                <button class="btn btn-warning rounded-pill fw-bold px-4 mt-3"><i class="fa-solid fa-floppy-disk me-1"></i> Save Homepage Settings</button>
            </div>
            <div class="col-xl-5">
                <div class="small fw-bold text-muted mb-2">LIVE HERO / LOOKBOOK PREVIEW</div>
                <div id="carouselDemo" class="qw-carousel-demo">
                    <div id="demoPlaceholder" class="qw-demo-placeholder {{ $previewSlide ? 'd-none' : '' }}"><i class="fa-regular fa-images"></i><span>Choose a Hero or Lookbook image</span></div>
                    <img id="demoImage" @if($previewSlide) src="{{ $previewSlide->image_url }}" @endif class="{{ $previewSlide ? '' : 'd-none' }}" alt="Carousel preview">
                    <div class="qw-demo-caption" id="demoCaption" style="left:{{ $editing ? request('text_x', $previewSlide?->text_x ?? 3) : ($previewSlide?->text_x ?? 3) }}%;top:{{ $editing ? request('text_y', $previewSlide?->text_y ?? 66) : ($previewSlide?->text_y ?? 66) }}%;bottom:auto"><h4 id="demoHeading">{{ $previewSlide?->heading ?: 'New collection' }}</h4><p id="demoSubheading">&ldquo;{{ $previewSlide?->subheading ?: 'Discover the latest looks' }}&rdquo;</p><a id="demoButton" class="qw-demo-button {{ $previewSlide?->link_url ? '' : 'd-none' }}" href="{{ $previewSlide?->link_url ?: '#' }}">{{ $previewSlide?->button_text ?: 'Explore' }}</a></div>
                    <span id="demoState" class="qw-demo-state">{{ $settings->enabled ? 'ON' : 'OFF' }}</span>
                </div>
                <small class="text-muted d-block mt-2">Section carousels show compact cards in a centered container. Drag the text on the preview to place it; the live view updates with your font, size and link.</small>
                <small class="d-block mt-2 p-2 rounded-3 bg-light text-dark" id="carouselOptionsPreview" aria-live="polite"></small>
            </div>
        </div>
    </div>
</form>

<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card border-0 rounded-4 shadow-sm h-100"><div class="card-body p-3 p-lg-4">
            <h5 class="fw-bold mb-3">{{ $editing ? 'Edit Hero / Lookbook Slide' : 'Add Hero / Lookbook Slide' }}</h5>
            <form action="{{ $editing ? route('admin.home-carousel.slides.update', $slide) : route('admin.home-carousel.slides.store') }}" method="POST" enctype="multipart/form-data">
                @csrf @if($editing) @method('PUT') @endif
                <div class="mb-3"><label class="form-label fw-semibold">Carousel section</label><select name="section_key" id="slideSection" class="form-select"><option value="hero" @selected($currentSlideSection === 'hero')>Hero banner</option><option value="lookbook" @selected($currentSlideSection === 'lookbook')>Lookbook</option></select></div>
                <div class="mb-3"><label class="form-label fw-semibold">Image <span class="text-danger">*</span></label>@if($editing)<div class="mb-2"><img src="{{ $slide->image_url }}" class="rounded-3 border" style="width:100%;height:100px;object-fit:contain;background:#211e1a" alt="Current slide"></div>@endif<input type="file" name="image" id="slideImage" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" {{ $editing ? '' : 'required' }}><small class="text-muted">Image is required; the full image is kept visible without crop.</small></div>
                <div class="mb-3"><label class="form-label fw-semibold">Main heading</label><input name="heading" id="slideHeading" class="form-control" maxlength="255" value="{{ old('heading', $editing ? $slide->heading : '') }}" placeholder="New collection"></div>
                <div class="row g-3 mb-3"><div class="col-md-4"><label class="form-label fw-semibold">Heading color</label><input type="color" name="heading_color" id="headingColor" class="form-control form-control-color w-100" value="{{ old('heading_color', $editing ? $slide->heading_color : '#ffffff') }}"></div><div class="col-md-5"><label class="form-label fw-semibold">Heading font</label><select name="heading_font" id="headingFont" class="form-select">@foreach($fontOptions as $key=>$label)<option value="{{ $key }}" @selected(old('heading_font', $editing ? $slide->heading_font : 'serif') === $key)>{{ $label }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-label fw-semibold">Size (px)</label><input type="number" name="heading_size" id="headingSize" class="form-control" min="12" max="72" value="{{ old('heading_size', $editing ? $slide->heading_size : 40) }}" required></div></div>
                <div class="mb-3"><label class="form-label fw-semibold">Subheading</label><input name="subheading" id="slideSubheading" class="form-control" maxlength="500" value="{{ old('subheading', $editing ? $slide->subheading : '') }}" placeholder="Discover your style"></div>
                <div class="row g-3 mb-3"><div class="col-md-3"><label class="form-label fw-semibold">Text color</label><input type="color" name="subheading_color" id="subheadingColor" class="form-control form-control-color w-100" value="{{ old('subheading_color', $editing ? $slide->subheading_color : '#ffffff') }}"></div><div class="col-md-3"><label class="form-label fw-semibold">Subheading font</label><select name="subheading_font" id="subheadingFont" class="form-select">@foreach($fontOptions as $key=>$label)<option value="{{ $key }}" @selected(old('subheading_font', $editing ? $slide->subheading_font : 'sans') === $key)>{{ $label }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label fw-semibold">Size</label><input type="number" name="subheading_size" id="subheadingSize" class="form-control" min="10" max="40" value="{{ old('subheading_size', $editing ? $slide->subheading_size : 17) }}" required></div><div class="col-md-2"><label class="form-label fw-semibold">Order</label><input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $editing ? $slide->sort_order : (($slides->max('sort_order') ?? 0) + 1)) }}" required></div><div class="col-md-2"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="active" @selected(old('status', $editing ? $slide->status : 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $editing ? $slide->status : '') === 'inactive')>Inactive</option></select></div></div>
                <div class="row g-3 mb-3"><div class="col-md-4"><label class="form-label fw-semibold">Button text</label><input type="text" name="button_text" id="slideButtonText" class="form-control" maxlength="60" value="{{ old('button_text', $editing ? $slide->button_text : '') }}" placeholder="Shop now"></div><div class="col-md-8"><label class="form-label fw-semibold">Link URL</label><input type="text" name="link_url" id="slideLinkUrl" class="form-control" maxlength="2048" value="{{ old('link_url', $editing ? $slide->link_url : '') }}" placeholder="https://... or /collections/new"><small class="text-muted">Use a full URL or a site path beginning with /.</small></div></div>
                <input type="hidden" name="text_x" id="slideTextX" value="{{ old('text_x', $editing ? request('text_x', $slide->text_x) : 3) }}"><input type="hidden" name="text_y" id="slideTextY" value="{{ old('text_y', $editing ? request('text_y', $slide->text_y) : 66) }}">
                @foreach($errors->all() as $error)<div class="text-danger small mb-1">{{ $error }}</div>@endforeach
                <button class="btn btn-warning rounded-pill fw-bold px-4"><i class="fa-solid fa-floppy-disk me-1"></i>{{ $editing ? 'Update Slide' : 'Add Slide' }}</button>
                @if($editing)<a href="{{ route('admin.home-carousel.index') }}" class="btn btn-outline-secondary rounded-pill ms-1">Cancel</a>@endif
            </form>
        </div></div>
    </div>
    <div class="col-xl-7"><div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-3 p-lg-4">
        <h5 class="fw-bold mb-3">Hero and Lookbook Slides</h5>
        @if($slides->isEmpty())<p class="text-muted text-center py-4 mb-0">Add an image slide for Hero or Lookbook.</p>@else
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Client view</th><th>Image</th><th>Section / Heading</th><th>Order</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($slides as $item)
                <tr>
                    <td>
                        <div class="qw-slide-row-preview" style="--row-art:url('{{ $item->image_url }}')">
                            <img src="{{ $item->image_url }}" alt="">
                            <div class="qw-slide-row-caption" style="left:{{ $item->text_x ?? 3 }}%;top:{{ $item->text_y ?? 66 }}%">
                                <strong data-full-size="{{ $item->heading_size ?? 40 }}" style="color:{{ $item->heading_color }};font-family:{{ $fontCss[$item->heading_font] ?? $fontCss['serif'] }};font-size:{{ min(20, $item->heading_size ?? 40) }}px">{{ $item->heading ?: 'New collection' }}</strong>
                                @if($item->subheading)<small data-full-size="{{ $item->subheading_size ?? 17 }}" style="color:{{ $item->subheading_color }};font-family:{{ $fontCss[$item->subheading_font ?? 'sans'] ?? $fontCss['sans'] }}">&ldquo;{{ $item->subheading }}&rdquo;</small>@endif
                                @if($item->link_url)<span>{{ $item->button_text ?: 'Explore' }}</span>@endif
                            </div>
                        </div>
                    </td>
                    <td><img src="{{ $item->image_url }}" class="rounded-3 border" style="width:78px;height:52px;object-fit:contain;background:#211e1a" alt="{{ $item->heading }}"></td>
                    <td><span class="badge bg-light text-dark border text-uppercase">{{ $item->section_key }}</span><div class="fw-semibold mt-1">{{ $item->heading ?: 'Image only' }}</div></td>
                    <td>{{ $item->sort_order }}</td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input qw-status-toggle" data-url="{{ route('admin.home-carousel.slides.toggle-status', $item) }}" @checked($item->status === 'active') id="statusToggle{{ $item->id }}" style="cursor:pointer" title="Toggle slide status">
                        </div>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.home-carousel.builder', $item->id) }}" class="btn btn-sm btn-danger fw-bold rounded-pill px-3 me-1" title="Open Canva-style Carousel Builder"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Carousel Builder</a>
                        <button type="button" class="btn btn-sm btn-outline-primary qw-view-slide" title="View client carousel" aria-label="View carousel preview"><i class="fa-regular fa-eye"></i></button>
                        <a href="{{ route('admin.home-carousel.slides.edit', $item) }}" class="btn btn-sm btn-outline-dark" title="Customize slide"><i class="fa-solid fa-pen"></i></a>
                        <form class="d-inline" method="POST" action="{{ route('admin.home-carousel.slides.destroy', $item) }}" onsubmit="return confirm('Delete this slide?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                    </td>
                </tr>
            @endforeach
        </tbody></table></div>@endif
</div></div></div>
</div>

<div class="qw-slide-modal" id="slidePreviewModal" hidden>
    <div class="qw-slide-modal-backdrop" data-close-slide-preview></div>
    <section class="qw-slide-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="slidePreviewTitle">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><h5 class="fw-bold mb-0" id="slidePreviewTitle">Design carousel slide</h5><small class="text-muted">Pick an element, drag it on the image, then style it here.</small></div><button type="button" class="btn-close" aria-label="Close editor" data-close-slide-preview></button></div>
        <div class="qw-story-editor">
            <div id="slideModalStage" class="qw-slide-modal-stage" aria-label="Carousel slide design canvas"></div>
            <aside class="qw-story-tools">
                <div class="d-flex gap-2 mb-3"><button type="button" class="btn btn-sm btn-outline-dark w-50" id="addStoryText"><i class="fa-solid fa-plus me-1"></i>Add text</button><button type="button" class="btn btn-sm btn-outline-dark w-50" id="addStoryLink"><i class="fa-solid fa-link me-1"></i>Add link</button></div>
                <label class="form-label small fw-bold" for="storyText">Selected text</label><textarea id="storyText" class="form-control mb-2" rows="2" maxlength="500"></textarea>
                <div id="storyUrlWrap" class="mb-2 d-none"><label class="form-label small fw-bold" for="storyUrl">Link URL</label><input id="storyUrl" class="form-control" placeholder="https://... or /shop"><small class="text-muted">Use a full URL or site path.</small></div>
                <div class="row g-2 mb-2"><div class="col-6"><label class="form-label small fw-bold" for="storyFont">Font</label><select id="storyFont" class="form-select form-select-sm">@foreach($fontOptions as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div><div class="col-6"><label class="form-label small fw-bold" for="storySize">Size</label><input id="storySize" class="form-control form-control-sm" type="number" min="10" max="84"></div></div>
                <div class="row g-2 mb-3"><div class="col-6"><label class="form-label small fw-bold" for="storyColor">Text color</label><input id="storyColor" type="color" class="form-control form-control-color w-100"></div><div class="col-6" id="storyBackgroundWrap"><label class="form-label small fw-bold" for="storyBackground">Button color</label><input id="storyBackground" type="color" class="form-control form-control-color w-100" value="#f0c75e"></div></div>
                <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-danger" id="deleteStoryElement">Delete selected</button><button type="button" class="btn btn-sm btn-warning fw-bold flex-grow-1" id="saveStoryDesign"><i class="fa-solid fa-floppy-disk me-1"></i>Save design</button></div>
                <div id="storySaveMessage" class="small mt-2" aria-live="polite"></div>
            </aside>
        </div>
        <input type="hidden" id="storyDesignCsrf" value="{{ csrf_token() }}">
    </section>
</div>

<div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-3 p-lg-4">
    <h5 class="fw-bold mb-1">Customer Reviews</h5><p class="small text-muted">Add approved customer reviews for the last carousel section. No reviews are shown until active entries are added.</p>
    <form action="{{ route('admin.home-carousel.testimonials.store') }}" method="POST" class="row g-2 align-items-end mb-3">@csrf
        <div class="col-md-2"><label class="form-label small fw-semibold">Customer</label><input class="form-control" name="customer_name" maxlength="120" required></div>
        <div class="col-md-5"><label class="form-label small fw-semibold">Review</label><input class="form-control" name="review" maxlength="1000" required></div>
        <div class="col-4 col-md-1"><label class="form-label small fw-semibold">Rating</label><select class="form-select" name="rating">@for($i=5;$i>=1;$i--)<option value="{{ $i }}">{{ $i }} ★</option>@endfor</select></div>
        <div class="col-4 col-md-1"><label class="form-label small fw-semibold">Order</label><input type="number" class="form-control" name="sort_order" value="{{ ($testimonials->max('sort_order') ?? 0) + 1 }}" min="0"></div>
        <div class="col-4 col-md-1"><label class="form-label small fw-semibold">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-2"><button class="btn btn-warning rounded-pill fw-bold w-100">Add Review</button></div>
    </form>
    @if($testimonials->isNotEmpty())<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Customer</th><th>Review</th><th>Rating</th><th>Status</th><th></th></tr></thead><tbody>@foreach($testimonials as $review)<tr><td>{{ $review->customer_name }}</td><td>{{ $review->review }}</td><td>{{ $review->rating }} / 5</td><td>{{ ucfirst($review->status) }}</td><td class="text-end"><form method="POST" action="{{ route('admin.home-carousel.testimonials.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form></td></tr>@endforeach</tbody></table></div>@endif
</div></div>

<style>
.qw-carousel-demo{position:relative;overflow:hidden;min-height:235px;background:radial-gradient(ellipse at 50% 35%,#3b3329,#211e1a 68%,#151412);border-radius:16px;color:white}.qw-carousel-demo img{width:100%;height:235px;object-fit:contain;object-position:center}.qw-demo-placeholder{height:235px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#fff9}.qw-demo-placeholder i{font-size:2.3rem}.qw-demo-caption{position:absolute;left:3%;top:66%;right:auto;bottom:auto;width:max-content;max-width:94%;padding:13px 16px;border-radius:10px;background:#171411b8;backdrop-filter:blur(8px);cursor:grab;touch-action:none;user-select:none}.qw-demo-caption.is-dragging{cursor:grabbing}.qw-demo-caption h4{font-size:1.3rem;font-weight:700;margin:0 0 5px;overflow-wrap:anywhere}.qw-demo-caption p{margin:0 0 7px;overflow-wrap:anywhere}.qw-demo-button,.qw-slide-row-caption span{display:inline-block;background:#f0c75e;color:#21180a;text-decoration:none;border-radius:20px;padding:5px 12px;font-size:.75rem;font-weight:800}.qw-demo-state{position:absolute;top:10px;right:10px;background:#0009;padding:3px 9px;border-radius:20px;font-size:.7rem;font-weight:700}.qw-slide-row-preview{position:relative;isolation:isolate;width:220px;height:110px;overflow:hidden;border-radius:10px;background:#211e1a}.qw-slide-row-preview:before{content:"";position:absolute;z-index:0;inset:-12px;background:var(--row-art) center/cover;filter:blur(12px);opacity:.9}.qw-slide-row-preview>img{position:relative;z-index:1;width:100%;height:100%;object-fit:contain}.qw-slide-row-caption{position:absolute;max-width:90%;padding:5px 7px;border-radius:6px;background:#171411bd;line-height:1.12}.qw-slide-row-caption strong,.qw-slide-row-caption small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.qw-slide-row-caption small{font-size:9px}.qw-slide-row-caption span{padding:2px 6px;font-size:8px}.qw-slide-modal[hidden]{display:none!important}.qw-slide-modal{position:fixed;z-index:1200;inset:0;display:grid;place-items:center;padding:20px}.qw-slide-modal-backdrop{position:absolute;inset:0;background:#100d0bd9;backdrop-filter:blur(5px)}.qw-slide-modal-dialog{position:relative;z-index:1;width:min(100%,980px);max-height:96vh;overflow:auto;background:#fff;border-radius:18px;padding:clamp(16px,3vw,30px);box-shadow:0 25px 80px #0007}.qw-slide-modal-stage{height:clamp(260px,58vh,560px);overflow:hidden;border-radius:14px;background:#211e1a}.qw-slide-modal-preview{width:100%!important;height:100%!important;border-radius:14px!important}.qw-slide-modal-preview>img{object-fit:contain}.qw-slide-modal-preview .qw-slide-row-caption{padding:12px 16px;border-radius:10px}.qw-slide-modal-preview .qw-slide-row-caption strong,.qw-slide-modal-preview .qw-slide-row-caption small{white-space:normal;text-overflow:clip;max-width:100%}.qw-slide-modal-preview .qw-slide-row-caption small{font-size:clamp(12px,2vw,22px)}
</style>
@endsection

@section('scripts')
<script>
(() => {
    const fontMap = @json($fontCss);
    const previewsBySection = @json($previewSlidesJson);
    const image = document.getElementById('demoImage');
    const placeholder = document.getElementById('demoPlaceholder');
    const heading = document.getElementById('demoHeading');
    const subheading = document.getElementById('demoSubheading');
    const caption = document.getElementById('demoCaption');
    const cta = document.getElementById('demoButton');
    let previewSlides = previewsBySection[@json($currentSlideSection)] || [];
    let current = 0;
    let timer;
    function syncText() {
        heading.textContent = document.getElementById('slideHeading')?.value || 'New collection';
        subheading.textContent = '\u201c' + (document.getElementById('slideSubheading')?.value || 'Discover the latest looks') + '\u201d';
        if (document.getElementById('headingColor')) heading.style.color = document.getElementById('headingColor').value;
        if (document.getElementById('subheadingColor')) subheading.style.color = document.getElementById('subheadingColor').value;
        if (document.getElementById('headingFont')) heading.style.fontFamily = fontMap[document.getElementById('headingFont').value];
        if (document.getElementById('subheadingFont')) subheading.style.fontFamily = fontMap[document.getElementById('subheadingFont').value];
        heading.style.fontSize = `${document.getElementById('headingSize')?.value || 40}px`;
        subheading.style.fontSize = `${document.getElementById('subheadingSize')?.value || 17}px`;
        const buttonText = document.getElementById('slideButtonText')?.value.trim();
        const linkUrl = document.getElementById('slideLinkUrl')?.value.trim();
        cta.textContent = buttonText || 'Explore'; cta.href = linkUrl || '#';
        cta.classList.toggle('d-none', !linkUrl);
        caption.style.left = `${document.getElementById('slideTextX')?.value || 3}%`;
        caption.style.top = `${document.getElementById('slideTextY')?.value || 66}%`;
    }
    document.querySelectorAll('#slideHeading,#slideSubheading,#headingColor,#subheadingColor,#headingFont,#headingSize,#subheadingFont,#subheadingSize,#slideButtonText,#slideLinkUrl').forEach(el => el.addEventListener('input', syncText));
    cta?.addEventListener('click', event => event.preventDefault());
    let dragOffset = null;
    caption?.addEventListener('pointerdown', event => { dragOffset = { x:event.clientX-caption.getBoundingClientRect().left, y:event.clientY-caption.getBoundingClientRect().top }; caption.setPointerCapture(event.pointerId); caption.classList.add('is-dragging'); event.preventDefault(); });
    caption?.addEventListener('pointermove', event => { if (!dragOffset) return; const area=document.getElementById('carouselDemo').getBoundingClientRect(); const box=caption.getBoundingClientRect(); const x=Math.max(0,Math.min(95,((event.clientX-area.left-dragOffset.x)/area.width)*100)); const y=Math.max(0,Math.min(90,((event.clientY-area.top-dragOffset.y)/area.height)*100)); document.getElementById('slideTextX').value=x.toFixed(2); document.getElementById('slideTextY').value=y.toFixed(2); syncText(); });
    const stopCaptionDrag = () => { dragOffset=null; caption?.classList.remove('is-dragging'); };
    caption?.addEventListener('pointerup', stopCaptionDrag); caption?.addEventListener('pointercancel', stopCaptionDrag);
    document.getElementById('slideImage')?.addEventListener('change', e => { const file=e.target.files?.[0]; if(file){image.src=URL.createObjectURL(file);image.classList.remove('d-none');placeholder.classList.add('d-none');} });
    function startPreview() {
        if (timer) clearInterval(timer);
        if (document.querySelector('[name="autoplay"]')?.checked !== false && previewSlides.length > 1) timer = setInterval(() => {
            current=(current+1)%previewSlides.length;
            const s=previewSlides[current]; image.src=s.image; image.classList.remove('d-none'); placeholder.classList.add('d-none');
            heading.textContent=s.heading || 'New collection'; subheading.textContent='\u201c'+(s.subheading || 'Discover the latest looks')+'\u201d';
            heading.style.color=s.heading_color; subheading.style.color=s.subheading_color; heading.style.fontFamily=fontMap[s.font] || fontMap.serif;
        }, Math.max(1500, Number(document.getElementById('carouselInterval').value)||5000));
    }
    document.getElementById('slideSection')?.addEventListener('change', e => {
        previewSlides = previewsBySection[e.target.value] || [];
        current = 0;
        const s = previewSlides[0];
        if (s) { image.src=s.image; image.classList.remove('d-none'); placeholder.classList.add('d-none'); heading.textContent=s.heading || 'New collection'; subheading.textContent='\u201c'+(s.subheading || 'Discover the latest looks')+'\u201d'; }
        else { image.classList.add('d-none'); placeholder.classList.remove('d-none'); heading.textContent='New collection'; subheading.textContent='\u201cDiscover the latest looks\u201d'; }
        startPreview();
    });
    startPreview();
    document.getElementById('carouselAnimation')?.addEventListener('change', e => document.getElementById('carouselDemo').dataset.animation=e.target.value);
    document.querySelector('[name="autoplay"]')?.addEventListener('change', startPreview);
    document.getElementById('carouselInterval')?.addEventListener('change', startPreview);
    document.getElementById('carouselDemo')?.addEventListener('mouseenter', () => { if (document.querySelector('[name="pause_on_hover"]')?.checked && timer) { clearInterval(timer); timer=null; } });
    document.getElementById('carouselDemo')?.addEventListener('mouseleave', () => { if (document.querySelector('[name="pause_on_hover"]')?.checked) startPreview(); });
    document.getElementById('heroEnabled')?.addEventListener('change', e => document.getElementById('demoState').textContent=e.target.checked?'ON':'OFF');
    const slideModal = document.getElementById('slidePreviewModal');
    const modalStage = document.getElementById('slideModalStage');
    const customizeLink = document.getElementById('slideModalCustomize');
    let modalDrag = null;
    function closeSlideModal() { slideModal.hidden=true; modalStage.innerHTML=''; document.body.style.overflow=''; }
    document.querySelectorAll('.qw-view-slide').forEach(button => button.addEventListener('click', () => {
        const row=button.closest('tr'), source=row.querySelector('.qw-slide-row-preview'), preview=source.cloneNode(true);
        preview.classList.add('qw-slide-modal-preview');
        const title=row.querySelector('td:nth-child(3) .fw-semibold')?.textContent.trim() || 'Carousel slide';
        document.getElementById('slidePreviewTitle').textContent=`${title} - client preview`;
        const heading=preview.querySelector('strong'), subheading=preview.querySelector('small');
        if(heading) heading.style.fontSize=`clamp(18px, 4vw, ${heading.dataset.fullSize || 40}px)`;
        if(subheading) subheading.style.fontSize=`clamp(12px, 2vw, ${subheading.dataset.fullSize || 17}px)`;
        modalStage.replaceChildren(preview);
        const editUrl=new URL(row.querySelector('a[title="Customize slide"]').href,window.location.href);
        const caption=preview.querySelector('.qw-slide-row-caption');
        const setCustomizePosition=()=>{editUrl.searchParams.set('text_x',parseFloat(caption.style.left)||0);editUrl.searchParams.set('text_y',parseFloat(caption.style.top)||0);customizeLink.href=editUrl.toString();};
        setCustomizePosition();
        caption.addEventListener('pointerdown',event=>{modalDrag={offsetX:event.clientX-caption.getBoundingClientRect().left,offsetY:event.clientY-caption.getBoundingClientRect().top};caption.setPointerCapture(event.pointerId);event.preventDefault();});
        caption.addEventListener('pointermove',event=>{if(!modalDrag)return;const bounds=preview.getBoundingClientRect(),box=caption.getBoundingClientRect();const maxX=Math.max(0,Math.min(95,100-box.width/bounds.width*100)),maxY=Math.max(0,Math.min(90,100-box.height/bounds.height*100));caption.style.left=`${Math.min(maxX,Math.max(0,(event.clientX-bounds.left-modalDrag.offsetX)/bounds.width*100))}%`;caption.style.top=`${Math.min(maxY,Math.max(0,(event.clientY-bounds.top-modalDrag.offsetY)/bounds.height*100))}%`;setCustomizePosition();});
        const stopModalDrag=()=>{modalDrag=null;};caption.addEventListener('pointerup',stopModalDrag);caption.addEventListener('pointercancel',stopModalDrag);
        slideModal.hidden=false;document.body.style.overflow='hidden';
    }));
    slideModal.querySelectorAll('[data-close-slide-preview]').forEach(button=>button.addEventListener('click',closeSlideModal));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!slideModal.hidden)closeSlideModal();});
    const optionsPreview = document.getElementById('carouselOptionsPreview');
    function syncCarouselOptionsPreview() {
        if (!optionsPreview) return;
        const value = name => document.querySelector(`[name="${name}"]`);
        const checked = name => value(name)?.checked;
        const text = name => value(name)?.value;
        const flags = [['loop','Loop'],['center_mode','Center'],['show_nav','Arrows'],['show_dots','Dots'],['autoplay','Autoplay'],['pause_on_hover','Pause on hover']].filter(([key]) => checked(key)).map(([,label]) => label);
        optionsPreview.textContent = `Desktop ${text('items_desktop')} · Tablet ${text('items_tablet')} · Mobile ${text('items_mobile')} cards | ${text('margin_px')}px gap | ${text('smart_speed_ms')}ms speed | ${text('interval_ms')}ms autoplay delay | ${flags.length ? flags.join(' · ') : 'No extra controls'}`;
    }
    document.querySelectorAll('[name="items_desktop"],[name="items_tablet"],[name="items_mobile"],[name="margin_px"],[name="smart_speed_ms"],[name="interval_ms"],[name="loop"],[name="center_mode"],[name="show_nav"],[name="show_dots"],[name="autoplay"],[name="pause_on_hover"]').forEach(el => el.addEventListener('input', syncCarouselOptionsPreview));
    document.querySelectorAll('.qw-status-toggle').forEach(input => {
        input.addEventListener('change', function() {
            const url = this.dataset.url;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.checked = data.status === 'active';
                }
            })
            .catch(err => {
                console.error('Failed to update status', err);
                this.checked = !this.checked;
            });
        });
    });
    syncCarouselOptionsPreview();
    syncText();
})();
</script>
@endsection

