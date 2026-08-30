@extends('layouts.app')

@section('title', $seoTitle ?? 'Quara Wardrobe | Online Fashion Store & Ladies Wear')
@section('meta_description', $seoDescription ?? 'Shop elegant, trendy & affordable ladies fashion, western wear, Korean tops, and stylish dresses at Quara Wardrobe online store. Fast pan-India delivery.')
@section('canonical_url', $canonicalUrl ?? route('home'))

@section('styles')
<style>
    @media (max-width: 375px) {
        .btn-select-buy {
            font-size: 0.68rem !important;
            padding: 5px 3px !important;
            letter-spacing: -0.2px;
            white-space: nowrap;
        }
    }
</style>
@endsection

@section('content')
<!-- Accessible H1 for SEO -->
<h1 class="visually-hidden">Quara Wardrobe — Online Fashion Store & Ladies Wear</h1>

<!-- Dynamic Home Main Content Master Area -->
@if($homeContent)
    <section class="qw-dynamic-home-content py-4 bg-white shadow-sm border-bottom">
        <div class="container">
            @if($homeContent->custom_css)
                <style>
                    {!! $homeContent->custom_css !!}
                </style>
            @endif

            @if($homeContent->image_position === 'top' && $homeContent->image_url)
                <div class="text-center mb-4">
                    <img src="{{ $homeContent->image_url }}" alt="Quara Banner" class="img-fluid rounded-4 shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                </div>
            @endif

            <div class="home-html-render">
                {!! $homeContent->content_html !!}
            </div>

            @if($homeContent->image_position === 'bottom' && $homeContent->image_url)
                <div class="text-center mt-4">
                    <img src="{{ $homeContent->image_url }}" alt="Quara Banner" class="img-fluid rounded-4 shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                </div>
            @endif
        </div>
    </section>
@endif

@if(($displayOrderBy ?? 'category') === 'product')
    @include('frontend.partials.home_product_section')
    @include('frontend.partials.home_category_section')
@else
    @include('frontend.partials.home_category_section')
    @include('frontend.partials.home_product_section')
@endif

<!-- Brand Values Callout -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-4 shadow-sm border h-100">
                    <i class="fa-solid fa-tags text-gold fs-1 mb-3"></i>
                    <h5 class="font-serif fw-bold">Affordable Fashion</h5>
                    <p class="small text-muted mb-0">High-trend ladies apparel designed for budget-conscious fashion lovers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-4 shadow-sm border h-100">
                    <i class="fa-solid fa-truck-fast text-gold fs-1 mb-3"></i>
                    <h5 class="font-serif fw-bold">Fast Pan-India Dispatch</h5>
                    <p class="small text-muted mb-0">Quick dispatch with real-time order tracking support.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-4 shadow-sm border h-100">
                    <i class="fa-brands fa-whatsapp text-gold fs-1 mb-3"></i>
                    <h5 class="font-serif fw-bold">Direct WhatsApp Care</h5>
                    <p class="small text-muted mb-0">Get instant sizing guidance and inquiry assistance on WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    let currentPage = {{ $products->currentPage() }};
    let hasMorePages = {{ $products->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    const shopUrl = "{{ url()->current() }}";

    function fetchNextPage() {
        if (!hasMorePages || isLoading) return;

        isLoading = true;
        const loadingEl = document.getElementById('infiniteScrollLoading');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const sentinelEl = document.getElementById('infiniteScrollSentinel');

        if (loadingEl) loadingEl.classList.remove('d-none');
        if (loadMoreBtn) loadMoreBtn.classList.add('d-none');

        // Build URL parameters preserving all current filter parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage + 1);

        const fetchUrl = shopUrl + '?' + urlParams.toString();

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                const grid = document.getElementById('productGridContainer');
                grid.insertAdjacentHTML('beforeend', data.html);

                currentPage = data.current_page;
                hasMorePages = data.has_more;

                // Update showing count text
                const countTextEl = document.getElementById('productCountText');
                if (countTextEl && data.count_text) {
                    countTextEl.textContent = data.count_text;
                }

                if (!hasMorePages) {
                    if (sentinelEl) sentinelEl.remove();
                    if (loadMoreBtn) loadMoreBtn.remove();
                    const notice = document.getElementById('noMoreProductsNotice');
                    if (notice) {
                        notice.classList.remove('d-none');
                        notice.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> You have viewed all ${data.total} products!`;
                    }
                } else {
                    if (loadMoreBtn) loadMoreBtn.classList.remove('d-none');
                }
            }
        })
        .catch(err => console.error('Infinite scroll error:', err))
        .finally(() => {
            isLoading = false;
            if (loadingEl) loadingEl.classList.add('d-none');
        });
    }

    // IntersectionObserver to automatically fetch next page on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const sentinel = document.getElementById('infiniteScrollSentinel');
        if (sentinel && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && hasMorePages && !isLoading) {
                    fetchNextPage();
                }
            }, {
                rootMargin: '250px' // Trigger 250px before reaching bottom
            });
            observer.observe(sentinel);
        }
    });

    function shareProductLink(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title + ' - ' + @js($siteName ?? 'Quara Wardrobe'),
                text: 'Check out ' + title + ' on ' + @js($siteName ?? 'Quara Wardrobe') + '!',
                url: url
            }).catch(() => {
                copyToClipboard(url);
            });
        } else {
            copyToClipboard(url);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Product link copied to clipboard!');
        });
    }
</script>
@endsection
