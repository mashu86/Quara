@php
    $sectionTitles = \App\Models\HomePageSection::TITLES;
    $sectionEnabled = fn($key) => (bool) ($homeSections->get($key)?->enabled ?? false);
    $sectionLimit = fn($key) => (int) ($homeSections->get($key)?->items_to_show ?? 8);
    $cardProducts = fn($key) => $sectionProducts[$key] ?? collect();
    $fontFamilies = ['serif'=>'Georgia,serif','sans'=>'Arial,sans-serif','trebuchet'=>'Trebuchet MS,sans-serif','classic'=>'Times New Roman,serif','system'=>'system-ui,sans-serif'];
    $allProductsRendered = false;
@endphp

@foreach($homeSections as $key => $section)
    @if($key === 'hero' && $sectionEnabled('hero') && $carouselSettings?->enabled && $carouselSlides->isNotEmpty())
        <section class="qw-home-section qw-hero-section qw-editorial-hero" aria-label="Hero carousel">
            <div class="qw-hero-layout">
                <div class="qw-hero-intro">
                    <p class="qw-hero-eyebrow"><span>KANNUR, KERALA</span><span aria-hidden="true">/</span><span>QUARA WARDROBE</span></p>
                    <p class="qw-hero-kicker">THE EVERYDAY / EXTRAORDINARY EDIT</p>
                    <h2 id="qw-hero-title">Good style.<br>Great finds.<br><em>All you.</em></h2>
                    <p class="qw-hero-description">A fresh perspective on everyday dressing.<br>Discover your next favourite at Quara.</p>
                    <div class="qw-hero-actions">
                        <a class="qw-hero-shop" href="{{ route('shop') }}">Explore styles <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="qw-hero-note">
                        <span class="qw-hero-note-icon" aria-hidden="true">✦</span>
                        <p><strong>MADE FOR YOUR EVERYDAY</strong><span>Thoughtful styles, easy to make your own.</span></p>
                    </div>
                </div>
                <div class="qw-hero-feature" role="region" aria-roledescription="carousel" aria-label="Hero Recommended">
                    <div class="qw-hero-stage">
                <div class="qw-hero-track {{ $carouselSettings->carousel_type === 'contained' ? 'qw-hero-contained' : '' }}" id="qw-track-hero" data-interval="{{ $carouselSettings->interval_ms ?? 5000 }}" data-animation="{{ $carouselSettings->animation ?? 'slide' }}" data-loop="{{ (int) ($carouselSettings->loop ?? true) }}" data-autoplay="{{ (int) ($carouselSettings->autoplay ?? true) }}" data-pause-hover="{{ (int) ($carouselSettings->pause_on_hover ?? true) }}" data-speed="{{ $carouselSettings->smart_speed_ms ?? 600 }}">
                    @foreach($carouselSlides as $slide)
                        @php
                            $headingRgb = sscanf(ltrim($slide->heading_color ?? '#ffffff', '#'), '%2x%2x%2x');
                            $headingBrightness = $headingRgb ? (($headingRgb[0] * 299 + $headingRgb[1] * 587 + $headingRgb[2] * 114) / 255000) : 1;
                            $captionSurface = $headingBrightness > 0.58 ? 'rgba(20,16,13,.78)' : 'rgba(255,250,241,.93)';
                        @endphp
                        <article class="qw-hero-card" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" @if(!$loop->first) inert aria-hidden="true" @endif style="--slide-art:url('{{ $slide->image_url }}');--caption-surface:{{ $captionSurface }}">
                            <picture class="qw-slide-picture">
                                @if($slide->has_mobile_image)
                                    <source media="(max-width: 767.98px)" srcset="{{ $slide->mobile_image_url }}">
                                @endif
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->heading ?: 'Quara Wardrobe collection' }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                            </picture>
                            @if(!empty($slide->overlay_items) && count($slide->overlay_items) > 0)
                                @foreach($slide->overlay_items as $overlay)
                                    @php
                                        $desktopLayout = array_merge($overlay, is_array($overlay['responsive']['desktop'] ?? null) ? $overlay['responsive']['desktop'] : []);
                                        $mobileLayout = array_merge($desktopLayout, is_array($overlay['responsive']['mobile'] ?? null) ? $overlay['responsive']['mobile'] : []);
                                        $elText = $overlay['text'] ?? '';
                                        $elTag = (!empty($overlay['linkHas']) && !empty($overlay['linkUrl'])) ? 'a' : 'span';
                                        $elFont = $overlay['font'] ?? 'Inter';
                                        $elSize = (int)($overlay['size'] ?? 20);
                                        $elWeight = $overlay['weight'] ?? '600';
                                        $elColor = $overlay['color'] ?? '#000000';
                                        $elBg = $overlay['bg'] ?? 'transparent';
                                        $elAlign = $overlay['align'] ?? 'left';
                                        $elRadius = $overlay['radius'] ?? 4;
                                        $elPadVal = (int)($overlay['padding'] ?? 4);
                                        $elPadding = $elPadVal ? ($elPadVal . 'px ' . ($elPadVal * 1.5) . 'px') : '4px 8px';
                                        $elHref = $overlay['linkUrl'] ?? '#';
                                        $elItalic = !empty($overlay['italic']) ? 'italic' : 'normal';
                                        $elUpper = !empty($overlay['uppercase']) ? 'uppercase' : 'none';
                                        $desktopX = max(0, min(100, (float) ($desktopLayout['x'] ?? 20)));
                                        $desktopY = max(0, min(100, (float) ($desktopLayout['y'] ?? 40)));
                                        $mobileX = max(0, min(100, (float) ($mobileLayout['x'] ?? $desktopX)));
                                        $mobileY = max(0, min(100, (float) ($mobileLayout['y'] ?? $desktopY)));
                                        $desktopWidth = max(0, (int) ($desktopLayout['width'] ?? 0));
                                        $desktopHeight = max(0, (int) ($desktopLayout['height'] ?? 0));
                                        $mobileWidth = max(0, (int) ($mobileLayout['width'] ?? $desktopWidth));
                                        $mobileHeight = max(0, (int) ($mobileLayout['height'] ?? $desktopHeight));
                                        $desktopSize = max(10, (int) ($desktopLayout['fontSize'] ?? $elSize));
                                        $mobileSize = max(10, (int) ($mobileLayout['fontSize'] ?? $desktopSize));
                                        $mobilePad = (int) ($mobileLayout['padding'] ?? $elPadVal);
                                        $mobilePadding = $mobilePad ? ($mobilePad . 'px ' . ($mobilePad * 1.5) . 'px') : '4px 8px';
                                        $elStyle = "position:absolute;left:{$desktopX}%;top:{$desktopY}%;width:" . ($desktopWidth ? $desktopWidth . 'px' : 'max-content') . ";height:" . ($desktopHeight ? $desktopHeight . 'px' : 'auto') . ";max-width:calc(100% - {$desktopX}%);font-family:'{$elFont}',sans-serif;--el-size:{$desktopSize};--mobile-x:{$mobileX}%;--mobile-y:{$mobileY}%;--mobile-width:" . ($mobileWidth ? $mobileWidth . 'px' : 'max-content') . ";--mobile-height:" . ($mobileHeight ? $mobileHeight . 'px' : 'auto') . ";--mobile-font-size:{$mobileSize}px;--mobile-padding:{$mobilePadding};font-size:{$desktopSize}px;font-weight:{$elWeight};color:{$elColor};background-color:{$elBg};text-align:{$elAlign};font-style:{$elItalic};text-transform:{$elUpper};border-radius:{$elRadius}px;padding:{$elPadding};text-decoration:none;z-index:3;line-height:1.2;box-sizing:border-box;";
                                    @endphp
                                    <{{ $elTag }} class="qw-story-element qw-overlay-item" style="{{ $elStyle }}" @if($elTag === 'a') href="{{ $elHref }}" @endif>{{ $elText }}</{{ $elTag }}>
                                @endforeach
                            @else
                                @if($slide->heading)<h2 class="qw-story-element qw-story-heading" style="left:{{ $slide->heading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->heading_y ?? $slide->text_y ?? 60 }}%;color:{{ $slide->heading_color }};font-family:{{ $fontFamilies[$slide->heading_font] ?? $fontFamilies['serif'] }};--hero-text-size:{{ min(36, $slide->heading_size ?? 28) }}px;font-size:{{ min(36, $slide->heading_size ?? 28) }}px">{{ $slide->heading }}</h2>@endif
                                @if($slide->subheading)<p class="qw-story-element qw-story-subheading" style="left:{{ $slide->subheading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->subheading_y ?? 74 }}%;color:{{ $slide->subheading_color }};font-family:{{ $fontFamilies[$slide->subheading_font ?? 'sans'] ?? $fontFamilies['sans'] }};--hero-text-size:{{ min(24, $slide->subheading_size ?? 15) }}px;font-size:{{ min(24, $slide->subheading_size ?? 15) }}px">&ldquo;{{ $slide->subheading }}&rdquo;</p>@endif
                                @if($slide->link_url)<a class="qw-story-element qw-hero-cta" style="left:{{ $slide->button_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->button_y ?? 84 }}%" href="{{ $slide->link_url }}">{{ $slide->button_text ?: 'Explore' }}</a>@endif
                            @endif
                        </article>
                    @endforeach
                </div>
                    </div>
                    @if($carouselSlides->count() > 1)<div class="qw-track-dots qw-hero-pagination" data-dots-for="qw-track-hero" aria-label="Choose hero slide"></div>@endif
                </div>
            </div>
        </section>
    @elseif($key === 'categories')
        @if(($displayOrderBy ?? 'category') === 'product' && !$allProductsRendered)
            @include('frontend.partials.home_product_section')
            @php $allProductsRendered = true; @endphp
        @endif
        @if(($categoryDisplayStyle ?? 'carousel') === 'carousel' && $sectionEnabled($key) && $categories->where('is_offer_category', false)->isNotEmpty())
            @php $carouselCategories = $categories->where('is_offer_category', false)->take($sectionLimit($key)); @endphp
            <section class="qw-home-section"><div class="container">
                @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Explore Quara', 'title'=>'Shop by Category', 'trackId'=>'qw-track-categories'])
                <div class="qw-horizontal-track" id="qw-track-categories" data-interval="{{ $carouselSettings->interval_ms ?? 5000 }}">
                    @foreach($carouselCategories as $category)
                        <a href="{{ route('category.products', $category->slug) }}" class="qw-category-slide-card">
                            <div class="qw-category-slide-image"><img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" loading="lazy"></div>
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div></section>
        @elseif($categoryDisplayStyle !== 'carousel')
            @include('frontend.partials.home_category_section')
        @endif
    @elseif($key === 'new_arrivals' && $sectionEnabled($key) && $cardProducts($key)->isNotEmpty())
        <section class="qw-home-section"><div class="container">
            @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Just arrived', 'title'=>'New Arrivals', 'trackId'=>'qw-track-new-arrivals'])
            @include('frontend.partials.home_product_carousel_cards', ['items'=>$cardProducts($key), 'trackId'=>'qw-track-new-arrivals', 'interval'=>$carouselSettings->interval_ms ?? 5000])
        </div></section>
    @elseif($key === 'best_sellers' && $sectionEnabled($key) && $cardProducts($key)->isNotEmpty())
        <section class="qw-home-section"><div class="container">
            @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Loved by our customers', 'title'=>'Best Sellers', 'trackId'=>'qw-track-best-sellers'])
            @include('frontend.partials.home_product_carousel_cards', ['items'=>$cardProducts($key), 'trackId'=>'qw-track-best-sellers', 'interval'=>$carouselSettings->interval_ms ?? 5000])
        </div></section>
    @elseif($key === 'offers' && $sectionEnabled($key) && $cardProducts($key)->isNotEmpty())
        <section class="qw-home-section qw-offer-section"><div class="container">
            @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Limited time styles', 'title'=>'Offers & Sale', 'trackId'=>'qw-track-offers'])
            @include('frontend.partials.home_product_carousel_cards', ['items'=>$cardProducts($key), 'trackId'=>'qw-track-offers', 'interval'=>$carouselSettings->interval_ms ?? 5000])
        </div></section>
    @elseif($key === 'lookbook' && $sectionEnabled($key) && $lookbookSlides->isNotEmpty())
        <section class="qw-home-section"><div class="container">
            @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Style inspiration', 'title'=>'The Lookbook', 'trackId'=>'qw-track-lookbook'])
            <div class="qw-horizontal-track qw-lookbook-track" id="qw-track-lookbook" data-interval="{{ $carouselSettings->interval_ms ?? 5000 }}">
                @foreach($lookbookSlides as $slide)
                    <article class="qw-lookbook-card" style="--slide-art:url('{{ $slide->image_url }}')">
                        <picture class="qw-slide-picture">
                            @if($slide->has_mobile_image)
                                <source media="(max-width: 767.98px)" srcset="{{ $slide->mobile_image_url }}">
                            @endif
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->heading ?: 'Quara Wardrobe lookbook' }}" loading="lazy">
                        </picture>
                        @if($slide->heading)<h3 class="qw-story-element qw-story-heading" style="left:{{ $slide->heading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->heading_y ?? $slide->text_y ?? 60 }}%;color:{{ $slide->heading_color }};font-family:{{ $fontFamilies[$slide->heading_font] ?? $fontFamilies['serif'] }};font-size:{{ min(48, $slide->heading_size ?? 40) }}px">{{ $slide->heading }}</h3>@endif
                        @if($slide->subheading)<p class="qw-story-element qw-story-subheading" style="left:{{ $slide->subheading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->subheading_y ?? 74 }}%;color:{{ $slide->subheading_color }};font-family:{{ $fontFamilies[$slide->subheading_font ?? 'sans'] ?? $fontFamilies['sans'] }};font-size:{{ min(32, $slide->subheading_size ?? 17) }}px">&ldquo;{{ $slide->subheading }}&rdquo;</p>@endif
                        @if($slide->link_url)<a class="qw-story-element qw-hero-cta" style="left:{{ $slide->button_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->button_y ?? 88 }}%" href="{{ $slide->link_url }}">{{ $slide->button_text ?: 'Explore' }}</a>@endif
                        @foreach($slide->overlay_items ?? [] as $overlay)
                            @php
                                $desktopLayout = array_merge($overlay, is_array($overlay['responsive']['desktop'] ?? null) ? $overlay['responsive']['desktop'] : []);
                                $mobileLayout = array_merge($desktopLayout, is_array($overlay['responsive']['mobile'] ?? null) ? $overlay['responsive']['mobile'] : []);
                                $overlayX = max(0, min(100, (float) ($desktopLayout['x'] ?? 5)));
                                $overlayY = max(0, min(100, (float) ($desktopLayout['y'] ?? 50)));
                                $mobileX = max(0, min(100, (float) ($mobileLayout['x'] ?? $overlayX)));
                                $mobileY = max(0, min(100, (float) ($mobileLayout['y'] ?? $overlayY)));
                                $overlaySize = max(10, (int) ($desktopLayout['fontSize'] ?? $overlay['size'] ?? 20));
                                $mobileSize = max(10, (int) ($mobileLayout['fontSize'] ?? $overlaySize));
                                $overlayWidth = max(0, (int) ($desktopLayout['width'] ?? 0));
                                $mobileWidth = max(0, (int) ($mobileLayout['width'] ?? $overlayWidth));
                                $overlayHeight = max(0, (int) ($desktopLayout['height'] ?? 0));
                                $mobileHeight = max(0, (int) ($mobileLayout['height'] ?? $overlayHeight));
                                $overlayPad = max(0, (int) ($overlay['padding'] ?? 4));
                                $mobilePad = max(0, (int) ($mobileLayout['padding'] ?? $overlayPad));
                                $overlayBg = $overlay['bg'] ?? $overlay['background'] ?? 'transparent';
                                $overlayUrl = $overlay['linkUrl'] ?? $overlay['url'] ?? '#';
                                $overlayIsLink = (!empty($overlay['linkHas']) && !empty($overlay['linkUrl'])) || (($overlay['type'] ?? '') === 'link');
                                $overlayStyle = "position:absolute;left:{$overlayX}%;top:{$overlayY}%;width:" . ($overlayWidth ? $overlayWidth . 'px' : 'max-content') . ";height:" . ($overlayHeight ? $overlayHeight . 'px' : 'auto') . ";max-width:calc(100% - {$overlayX}%);font-family:'" . ($overlay['font'] ?? 'Inter') . "',sans-serif;font-size:{$overlaySize}px;--mobile-x:{$mobileX}%;--mobile-y:{$mobileY}%;--mobile-width:" . ($mobileWidth ? $mobileWidth . 'px' : 'max-content') . ";--mobile-height:" . ($mobileHeight ? $mobileHeight . 'px' : 'auto') . ";--mobile-font-size:{$mobileSize}px;--mobile-padding:" . ($mobilePad ? ($mobilePad . 'px ' . ($mobilePad * 1.5) . 'px') : '4px 8px') . ";font-weight:" . ($overlay['weight'] ?? '600') . ";color:" . ($overlay['color'] ?? '#ffffff') . ";background-color:{$overlayBg};border-radius:" . ($overlay['radius'] ?? 4) . "px;padding:" . ($overlayPad ? ($overlayPad . 'px ' . ($overlayPad * 1.5) . 'px') : '4px 8px') . ";text-align:" . ($overlay['align'] ?? 'left') . ";font-style:" . (!empty($overlay['italic']) ? 'italic' : 'normal') . ";text-transform:" . (!empty($overlay['uppercase']) ? 'uppercase' : 'none') . ";text-decoration:none;box-sizing:border-box;line-height:1.2;z-index:3;";
                            @endphp
                            @if($overlayIsLink)
                                <a class="qw-story-element qw-overlay-item" style="{{ $overlayStyle }}" href="{{ $overlayUrl }}">{{ $overlay['text'] ?? '' }}</a>
                            @else
                                <span class="qw-story-element qw-overlay-item" style="{{ $overlayStyle }}">{{ $overlay['text'] ?? '' }}</span>
                            @endif
                        @endforeach
                    </article>
                @endforeach
            </div>
        </div></section>
    @elseif($key === 'reviews' && $sectionEnabled($key) && ($homeTestimonials->isNotEmpty() || $instagramLink))
        <section class="qw-home-section qw-reviews-section"><div class="container">
            @include('frontend.partials.home_carousel_heading', ['eyebrow'=>'Kind words', 'title'=>'Customer Love', 'trackId'=>'qw-track-reviews'])
            <div class="qw-horizontal-track qw-review-track" id="qw-track-reviews" data-interval="{{ $carouselSettings->interval_ms ?? 5000 }}">
                @foreach($homeTestimonials as $review)<article class="qw-review-card"><div class="qw-review-stars" aria-label="{{ $review->rating }} out of 5 stars">@for($star=1;$star<=5;$star++)<i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor</div><blockquote>&ldquo;{{ $review->review }}&rdquo;</blockquote><div class="qw-review-customer">{{ $review->customer_name }}</div></article>@endforeach
                @if($homeTestimonials->isEmpty() && $instagramLink)<a class="qw-review-card qw-instagram-card" href="{{ $instagramLink->formatted_link }}" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i><strong>Follow Quara Wardrobe</strong><span>See our latest styles on Instagram</span></a>@endif
            </div>
            @if($instagramLink)<div class="text-center mt-3"><a href="{{ $instagramLink->formatted_link }}" target="_blank" rel="noopener" class="qw-instagram-link"><i class="fa-brands fa-instagram me-2"></i>Follow us on Instagram</a></div>@endif
        </div></section>
    @endif
@endforeach

@if(!$allProductsRendered)
    @include('frontend.partials.home_product_section')
@endif

<style>
.qw-slide-picture, .qw-editorial-hero .qw-hero-card picture, .qw-lookbook-card picture { display: block !important; position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; }
.qw-slide-picture img, .qw-editorial-hero .qw-hero-card picture img, .qw-lookbook-card picture img { display: block !important; position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; object-fit: contain !important; object-position: center !important; }
@media(max-width:767.98px){.qw-editorial-hero .qw-overlay-item,.qw-lookbook-card .qw-overlay-item{left:var(--mobile-x)!important;top:var(--mobile-y)!important;width:var(--mobile-width, max-content)!important;height:var(--mobile-height, auto)!important;font-size:var(--mobile-font-size)!important;padding:var(--mobile-padding)!important;max-width:calc(100% - var(--mobile-x))!important;}}
@media(min-width:768px) and (max-width:991.98px){.qw-hero-track{--hero-items:var(--hero-tablet,1.5)}}
@media(max-width:767.98px){.qw-home-section{padding:0.85rem 0}.qw-horizontal-track{gap:12px}.qw-horizontal-track>*{flex-basis:56%}.qw-hero-track{--hero-items:var(--hero-mobile,1.04);--hero-gap:12px;aspect-ratio:var(--hero-image-ratio, 16 / 9);height:auto !important}.qw-editorial-hero .qw-hero-card{height:100% !important;border-radius:12px}.qw-category-slide-image{height:clamp(130px,38vw,185px)}.qw-product-slide{flex-basis:57%}.qw-product-slide-image{height:clamp(195px,62vw,280px)}.qw-lookbook-card{flex-basis:88%!important;height:clamp(260px,76vw,360px)}.qw-review-card{flex-basis:90%!important}.qw-section-heading{margin-bottom:10px}.qw-overlay-item{font-size:max(10px, calc(var(--el-size, 16) * 0.52 * 1px)) !important;padding:2px 6px !important;max-width:90% !important;word-wrap:break-word !important;white-space:normal !important;line-height:1.15 !important;}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.qw-section-heading').forEach(header => {
        const trackId=header.dataset.trackId, track=document.getElementById(trackId); if(!track) return;
        header.querySelectorAll('[data-scroll-dir]').forEach(button => button.addEventListener('click', () => track.scrollBy({left:Number(button.dataset.scrollDir)*track.clientWidth*.75,behavior:'smooth'})));
    });
    document.querySelectorAll('.qw-horizontal-track').forEach(track => {
        const isHero=track.classList.contains('qw-hero-track');
        const slides=[...track.children].filter(slide=>!slide.classList.contains('qw-hero-nav'));
        const dots=document.querySelector(`[data-dots-for="${track.id}"]`);
        const targetFor=index=>{const slide=slides[index];return slide ? Math.max(0,slide.offsetLeft-track.offsetLeft-(track.dataset.center==='1'?(track.clientWidth-slide.clientWidth)/2:0)) : 0;};
        const goTo=(index,speed=450)=>{const target=targetFor(index);if(!isHero){track.scrollTo({left:target,behavior:'smooth'});return;}const start=track.scrollLeft,delta=target-start,began=performance.now(),duration=Math.max(60,speed);const frame=now=>{const t=Math.min(1,(now-began)/duration),ease=1-Math.pow(1-t,3);track.scrollLeft=start+delta*ease;if(t<1)requestAnimationFrame(frame);};requestAnimationFrame(frame);};
        if(dots){dots.innerHTML=slides.map((_,i)=>`<button type="button" aria-label="Go to banner ${i+1}" class="${i===0?'active':''}"></button>`).join('');dots.querySelectorAll('button').forEach((button,i)=>button.addEventListener('click',()=>goTo(i,Number(track.dataset.speed)||450)));track.addEventListener('scroll',()=>{let closest=0,distance=Infinity;slides.forEach((slide,i)=>{const d=Math.abs(targetFor(i)-track.scrollLeft);if(d<distance){distance=d;closest=i;}});dots.querySelectorAll('button').forEach((b,i)=>b.classList.toggle('active',i===closest));},{passive:true});}
        if(isHero){track.querySelectorAll('[data-hero-dir]').forEach(button=>button.addEventListener('click',()=>{let current=0,distance=Infinity;slides.forEach((slide,i)=>{const d=Math.abs(targetFor(i)-track.scrollLeft);if(d<distance){distance=d;current=i;}});let next=current+Number(button.dataset.heroDir);if(next<0)next=track.dataset.loop==='1'?slides.length-1:0;if(next>=slides.length)next=track.dataset.loop==='1'?0:slides.length-1;goTo(next,Number(track.dataset.speed)||450);}));}
        if(isHero&&track.dataset.center==='1')requestAnimationFrame(()=>goTo(0,Number(track.dataset.speed)||450));
        const interval=Number(track.dataset.interval)||0;
        const autoplay=isHero?track.dataset.autoplay==='1':true;
        if(!autoplay||interval<1500||slides.length<2)return;
        let timer;
        const start=()=>{if(timer)return;timer=setInterval(()=>{if(document.hidden)return;const move=()=>{if(isHero){let current=0,distance=Infinity;slides.forEach((slide,i)=>{const d=Math.abs(targetFor(i)-track.scrollLeft);if(d<distance){distance=d;current=i;}});if(current===slides.length-1&&track.dataset.loop!=='1')return;goTo(current===slides.length-1?0:current+1,Number(track.dataset.speed)||450);return;}const end=track.scrollWidth-track.clientWidth;if(track.scrollLeft>=end-8)track.scrollTo({left:0,behavior:'smooth'});else track.scrollBy({left:track.clientWidth*.78,behavior:'smooth'});};if(track.dataset.animation==='fade'){track.classList.add('is-fading');setTimeout(()=>{move();setTimeout(()=>track.classList.remove('is-fading'),280);},130);}else move();},interval);};
        const stop=()=>{clearInterval(timer);timer=null;};
        if(!isHero||track.dataset.pauseHover==='1'){track.addEventListener('mouseenter',stop);track.addEventListener('mouseleave',start);}start();
    });
});
</script>
