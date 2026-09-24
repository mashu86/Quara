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
        <section class="qw-home-section qw-hero-section py-2 py-md-3">
            <div class="container">
                <div class="qw-horizontal-track qw-hero-track {{ $carouselSettings->carousel_type === 'contained' ? 'qw-hero-contained' : '' }}" id="qw-track-hero" data-interval="{{ $carouselSettings->interval_ms ?? 5000 }}" data-animation="{{ $carouselSettings->animation ?? 'slide' }}" data-loop="{{ (int) ($carouselSettings->loop ?? true) }}" data-autoplay="{{ (int) ($carouselSettings->autoplay ?? true) }}" data-pause-hover="{{ (int) ($carouselSettings->pause_on_hover ?? true) }}" data-nav="{{ (int) ($carouselSettings->show_nav ?? true) }}" data-dots="{{ (int) ($carouselSettings->show_dots ?? true) }}" data-center="{{ (int) ($carouselSettings->center_mode ?? false) }}" data-speed="{{ $carouselSettings->smart_speed_ms ?? 450 }}" style="--hero-desktop:{{ $carouselSettings->items_desktop ?? 2.2 }};--hero-tablet:{{ $carouselSettings->items_tablet ?? 1.5 }};--hero-mobile:{{ $carouselSettings->items_mobile ?? 1.12 }};--hero-gap:{{ $carouselSettings->margin_px ?? 16 }}px">
                    @foreach($carouselSlides as $slide)
                        @php
                            $headingRgb = sscanf(ltrim($slide->heading_color ?? '#ffffff', '#'), '%2x%2x%2x');
                            $headingBrightness = $headingRgb ? (($headingRgb[0] * 299 + $headingRgb[1] * 587 + $headingRgb[2] * 114) / 255000) : 1;
                            $captionSurface = $headingBrightness > 0.58 ? 'rgba(20,16,13,.78)' : 'rgba(255,250,241,.93)';
                        @endphp
                        <article class="qw-hero-card" style="--slide-art:url('{{ $slide->image_url }}');--caption-surface:{{ $captionSurface }}">
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->heading ?: 'Quara Wardrobe collection' }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                            @if(!empty($slide->overlay_items) && count($slide->overlay_items) > 0)
                                @foreach($slide->overlay_items as $overlay)
                                    @php
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
                                        $elStyle = "position:absolute;left:{$overlay['x']}%;top:{$overlay['y']}%;font-family:'{$elFont}',sans-serif;--el-size:{$elSize};font-size:{$elSize}px;font-weight:{$elWeight};color:{$elColor};background-color:{$elBg};text-align:{$elAlign};font-style:{$elItalic};text-transform:{$elUpper};border-radius:{$elRadius}px;padding:{$elPadding};text-decoration:none;z-index:3;line-height:1.2;";
                                    @endphp
                                    <{{ $elTag }} class="qw-story-element qw-overlay-item" style="{{ $elStyle }}" @if($elTag === 'a') href="{{ $elHref }}" @endif>{{ $elText }}</{{ $elTag }}>
                                @endforeach
                            @else
                                @if($slide->heading)<h2 class="qw-story-element qw-story-heading" style="left:{{ $slide->heading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->heading_y ?? $slide->text_y ?? 60 }}%;color:{{ $slide->heading_color }};font-family:{{ $fontFamilies[$slide->heading_font] ?? $fontFamilies['serif'] }};font-size:{{ min(36, $slide->heading_size ?? 28) }}px">{{ $slide->heading }}</h2>@endif
                                @if($slide->subheading)<p class="qw-story-element qw-story-subheading" style="left:{{ $slide->subheading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->subheading_y ?? 74 }}%;color:{{ $slide->subheading_color }};font-family:{{ $fontFamilies[$slide->subheading_font ?? 'sans'] ?? $fontFamilies['sans'] }};font-size:{{ min(24, $slide->subheading_size ?? 15) }}px">&ldquo;{{ $slide->subheading }}&rdquo;</p>@endif
                                @if($slide->link_url)<a class="qw-story-element qw-hero-cta" style="left:{{ $slide->button_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->button_y ?? 84 }}%" href="{{ $slide->link_url }}">{{ $slide->button_text ?: 'Explore' }}</a>@endif
                            @endif
                        </article>
                    @endforeach
                    @if($carouselSettings->show_nav ?? true)<button type="button" class="qw-hero-nav qw-hero-prev" data-hero-dir="-1" aria-label="Previous banner"><i class="fa-solid fa-chevron-left"></i></button><button type="button" class="qw-hero-nav qw-hero-next" data-hero-dir="1" aria-label="Next banner"><i class="fa-solid fa-chevron-right"></i></button>@endif
                </div>
                @if(($carouselSettings->show_dots ?? true) && $carouselSlides->count() > 1)<div class="qw-track-dots mt-2.5" data-dots-for="qw-track-hero"></div>@endif
            </div>
        </section>
    @elseif($key === 'categories')
        @if(($displayOrderBy ?? 'category') === 'product' && !$allProductsRendered)
            @include('frontend.partials.home_product_section')
            @php($allProductsRendered = true)
        @endif
        @if(($categoryDisplayStyle ?? 'carousel') === 'carousel' && $sectionEnabled($key) && $categories->where('is_offer_category', false)->isNotEmpty())
            @php($carouselCategories = $categories->where('is_offer_category', false)->take($sectionLimit($key)))
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
                @foreach($lookbookSlides as $slide)<article class="qw-lookbook-card" style="--slide-art:url('{{ $slide->image_url }}')"><img src="{{ $slide->image_url }}" alt="{{ $slide->heading ?: 'Quara Wardrobe lookbook' }}" loading="lazy">@if($slide->heading)<h3 class="qw-story-element qw-story-heading" style="left:{{ $slide->heading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->heading_y ?? $slide->text_y ?? 60 }}%;color:{{ $slide->heading_color }};font-family:{{ $fontFamilies[$slide->heading_font] ?? $fontFamilies['serif'] }};font-size:{{ min(48, $slide->heading_size ?? 40) }}px">{{ $slide->heading }}</h3>@endif @if($slide->subheading)<p class="qw-story-element qw-story-subheading" style="left:{{ $slide->subheading_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->subheading_y ?? 74 }}%;color:{{ $slide->subheading_color }};font-family:{{ $fontFamilies[$slide->subheading_font ?? 'sans'] ?? $fontFamilies['sans'] }};font-size:{{ min(32, $slide->subheading_size ?? 17) }}px">&ldquo;{{ $slide->subheading }}&rdquo;</p>@endif @if($slide->link_url)<a class="qw-story-element qw-hero-cta" style="left:{{ $slide->button_x ?? $slide->text_x ?? 3 }}%;top:{{ $slide->button_y ?? 88 }}%" href="{{ $slide->link_url }}">{{ $slide->button_text ?: 'Explore' }}</a>@endif @foreach($slide->overlay_items ?? [] as $overlay) @if(($overlay['type'] ?? 'text') === 'link')<a class="qw-story-element qw-overlay-link" style="left:{{ $overlay['x'] ?? 5 }}%;top:{{ $overlay['y'] ?? 50 }}%;color:{{ $overlay['color'] ?? '#ffffff' }};background:{{ $overlay['background'] ?? '#f0c75e' }};font-family:{{ $fontFamilies[$overlay['font'] ?? 'sans'] ?? $fontFamilies['sans'] }};font-size:{{ min(84, $overlay['size'] ?? 18) }}px" href="{{ $overlay['url'] ?? '#' }}">{{ $overlay['text'] ?? '' }}</a>@else<span class="qw-story-element qw-overlay-text" style="left:{{ $overlay['x'] ?? 5 }}%;top:{{ $overlay['y'] ?? 50 }}%;color:{{ $overlay['color'] ?? '#ffffff' }};font-family:{{ $fontFamilies[$overlay['font'] ?? 'sans'] ?? $fontFamilies['sans'] }};font-size:{{ min(84, $overlay['size'] ?? 24) }}px">{{ $overlay['text'] ?? '' }}</span>@endif @endforeach</article>@endforeach
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
.qw-home-section{padding:1.2rem 0;background:#fff;border-bottom:1px solid #eee}.qw-home-section:nth-of-type(even){background:#fcfbf8}.qw-section-heading{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:14px}.qw-section-eyebrow{display:block;color:#9b7628;text-transform:uppercase;font-size:.68rem;font-weight:800;letter-spacing:.16em;margin-bottom:3px}.qw-section-heading h2{font:700 clamp(1.2rem,2vw,1.65rem) Georgia,serif;margin:0;color:#201b17}.qw-track-controls{display:flex;gap:7px}.qw-track-controls button{width:34px;height:34px;border:1px solid #ddd;border-radius:50%;background:#fff;color:#25211c;display:inline-grid;place-items:center}.qw-track-controls button:hover{background:#16130f;color:#fff;border-color:#16130f}.qw-horizontal-track{position:relative;display:flex;gap:var(--hero-gap,16px);overflow-x:auto;overscroll-behavior-inline:contain;scroll-snap-type:x mandatory;scrollbar-width:none;padding:3px 1px 9px;scroll-behavior:smooth;transition:opacity .25s ease}.qw-horizontal-track.is-fading{opacity:.45}.qw-horizontal-track::-webkit-scrollbar{display:none}.qw-horizontal-track>*{scroll-snap-align:start;flex:0 0 22.5%;min-width:0}.qw-hero-track{--hero-items:var(--hero-desktop,2.2)}.qw-hero-track>*:not(.qw-hero-nav){flex:0 0 calc((100% - (var(--hero-gap,16px) * (var(--hero-items) - 1))) / var(--hero-items));}.qw-hero-card,.qw-lookbook-card{isolation:isolate}.qw-hero-card{height:clamp(180px,26vw,320px);position:relative;overflow:hidden;border-radius:16px;background:#181512;box-shadow:0 4px 16px rgba(0,0,0,0.08);transition:transform .25s ease,box-shadow .25s ease}.qw-hero-card::before,.qw-lookbook-card::before{content:"";position:absolute;z-index:0;inset:-20px;background-image:var(--slide-art);background-size:cover;background-position:center;filter:blur(22px) brightness(0.92) saturate(1.1);opacity:0.88;transform:scale(1.1)}.qw-hero-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.14)}.qw-hero-contained .qw-hero-card{border:1px solid #dbcba9;box-shadow:0 8px 24px #24180e18}.qw-hero-card img{position:relative;z-index:1;width:100%;height:100%;object-fit:contain;object-position:center;border-radius:16px}.qw-lookbook-card img{position:relative;z-index:1;width:100%;height:100%;object-fit:cover;object-position:center}.qw-hero-copy{position:absolute;z-index:2;left:18px;bottom:18px;right:auto;max-width:calc(100% - 36px);width:max-content;padding:10px 14px;border:1px solid #ffffff80;border-radius:12px;background:var(--caption-surface,rgba(20,16,13,.82));backdrop-filter:blur(10px);box-shadow:0 6px 18px #0003}.qw-hero-copy h2{font-size:clamp(1.1rem,2vw,1.6rem);font-weight:700;margin:0 0 4px;text-shadow:0 1px 5px #0003}.qw-hero-copy p{margin:0 0 6px;text-shadow:0 1px 4px #0002}.qw-hero-cta{display:inline-block;padding:5px 12px;border-radius:30px;background:#f0c75e;color:#21180a;text-decoration:none;font-size:.75rem;font-weight:800;box-shadow:0 3px 10px #0003;z-index:4;position:relative}.qw-hero-cta:hover{background:#ffe08a;color:#21180a}.qw-hero-nav{position:absolute;z-index:5;top:50%;transform:translateY(-50%);width:36px;height:36px;border:1px solid #ffffff99;border-radius:50%;background:rgba(20,16,13,0.65);color:#fff;display:grid;place-items:center;backdrop-filter:blur(6px);cursor:pointer;transition:background .2s ease}.qw-hero-nav:hover{background:rgba(20,16,13,0.9)}.qw-hero-prev{left:10px}.qw-hero-next{right:10px}.qw-track-dots{display:flex;justify-content:center;gap:6px;margin-top:10px}.qw-track-dots button{border:0;padding:0;width:8px;height:8px;border-radius:50%;background:#dcdcdc;transition:all .25s ease;cursor:pointer}.qw-track-dots button.active{width:22px;border-radius:10px;background:#9b7628}.qw-category-slide-card{text-decoration:none;text-align:center;color:#231e18;font-weight:650;font-size:.88rem}.qw-category-slide-image{height:clamp(110px,14vw,165px);background:#f1eee8;border-radius:13px;overflow:hidden;margin-bottom:7px}.qw-category-slide-image img{width:100%;height:100%;object-fit:cover}.qw-product-slide{flex-basis:22.5%;display:block;border:1px solid #eee;border-radius:13px;overflow:hidden;background:#fff;color:#221d17;text-decoration:none;box-shadow:0 5px 16px #21170c0d}.qw-product-slide-image{height:clamp(180px,24vw,290px);position:relative;background:#f5f2ed;display:grid;place-items:center}.qw-product-slide-image img{width:100%;height:100%;object-fit:contain}.qw-product-slide-body{padding:10px 11px 12px}.qw-product-slide-title{font-size:.88rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0 0 5px}.qw-product-slide-price{font-size:.87rem;font-weight:800}.qw-product-slide-old{font-size:.72rem;color:#888;text-decoration:line-through;margin-left:5px}.qw-sale-badge{position:absolute;top:9px;left:9px;background:#941e32;color:#fff;border-radius:20px;padding:4px 8px;font-size:.63rem;font-weight:800}.qw-lookbook-card{height:clamp(200px,25vw,320px);position:relative;isolation:isolate;overflow:hidden;border-radius:15px;background:#211c18;flex-basis:38%!important}.qw-lookbook-card>div{position:absolute;z-index:2;inset:auto 0 0;padding:24px 16px 14px;background:linear-gradient(transparent,#000c);color:#fff}.qw-lookbook-card h3{font:700 1.25rem Georgia,serif;margin:0}.qw-lookbook-card p{margin:3px 0 0;font-size:.84rem}.qw-review-card{flex-basis:31%!important;padding:20px;border:1px solid #eee;border-radius:15px;background:#fff;box-shadow:0 5px 18px #21170c0b}.qw-review-stars{color:#bd963e;font-size:.8rem;letter-spacing:2px}.qw-review-card blockquote{font:italic 1rem Georgia,serif;line-height:1.55;margin:12px 0;color:#3f3932}.qw-review-customer{font-weight:750;font-size:.82rem}.qw-instagram-card{display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:10px;color:#53324b;text-decoration:none}.qw-instagram-card i{font-size:2rem}.qw-instagram-card span{font-size:.85rem;color:#777}.qw-instagram-link{color:#6e3f5e;text-decoration:none;font-weight:700}.qw-offer-section{background:#fbf7ef!important}.qw-product-slide:hover{transform:translateY(-2px);box-shadow:0 10px 22px #21170c18;transition:.2s}
@media(min-width:768px) and (max-width:991.98px){.qw-hero-track{--hero-items:var(--hero-tablet,1.5)}}
@media(max-width:767.98px){.qw-home-section{padding:0.85rem 0}.qw-horizontal-track{gap:12px}.qw-horizontal-track>*{flex-basis:42%}.qw-hero-track{--hero-items:var(--hero-mobile,1.12);--hero-gap:12px}.qw-hero-card{height:clamp(210px,58vw,280px);border-radius:12px}.qw-hero-card img{object-fit:cover !important}.qw-category-slide-image{height:105px}.qw-product-slide{flex-basis:43%}.qw-product-slide-image{height:clamp(170px,56vw,240px)}.qw-lookbook-card{flex-basis:78%!important;height:230px}.qw-review-card{flex-basis:82%!important}.qw-section-heading{margin-bottom:10px}.qw-overlay-item{font-size:max(10px, calc(var(--el-size, 16) * 0.52 * 1px)) !important;padding:2px 6px !important;max-width:90% !important;word-wrap:break-word !important;white-space:normal !important;line-height:1.15 !important;}}
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
        const goTo=(index,speed=450)=>{const target=targetFor(index);if(!isHero){track.scrollTo({left:target,behavior:'smooth'});return;}const start=track.scrollLeft,delta=target-start,began=performance.now(),duration=Math.max(100,speed);const frame=now=>{const t=Math.min(1,(now-began)/duration),ease=1-Math.pow(1-t,3);track.scrollLeft=start+delta*ease;if(t<1)requestAnimationFrame(frame);};requestAnimationFrame(frame);};
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
