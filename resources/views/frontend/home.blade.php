@extends('layouts.app')

@section('title', $siteName . ' - Trendy & Affordable Ladies Wear')

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

<!-- Categories Section -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="text-center mb-4">
            <span class="text-gold text-uppercase fw-bold tracking-wider small">CHIC COLLECTIONS</span>
            <h2 class="font-serif display-6 fw-bold fs-3 fs-md-2 mb-2">SHOP BY CATEGORY</h2>
            <div class="mx-auto bg-gold" style="width: 50px; height: 3px;"></div>
        </div>

        <div class="row g-3">
            @foreach($categories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none">
                        <div class="qw-category-card">
                            @if($category->background_image)
                                <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="qw-category-bg" loading="lazy">
                            @else
                                <div class="qw-category-bg bg-black" role="img" aria-label="{{ $category->name }}"></div>
                            @endif
                            <div class="qw-category-overlay">
                                <h4 class="font-serif fw-bold mb-1" style="color: {{ $category->text_color }}; text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                                    {{ $category->name }}
                                </h4>
                                <span class="badge bg-gold rounded-pill px-3 py-2 small shadow-sm">
                                    {{ $category->products_count }} Items
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products Grid -->
<section class="py-4 py-md-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <div>
                <span class="text-gold text-uppercase fw-bold small">TRENDING NOW</span>
                <h2 class="font-serif display-6 fw-bold mb-0 fs-3 fs-md-2">NEW ARRIVALS</h2>
            </div>
            <a href="{{ route('shop') }}" class="btn btn-qw-outline btn-sm rounded-pill px-3 py-2 mt-3 mt-md-0 fw-semibold" style="font-size: 0.8rem;">VIEW ALL PRODUCTS <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            @forelse($featuredProducts as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="qw-product-card h-100 d-flex flex-column">
                        @if($product->discount_type !== 'none' && $product->price > 0)
                            @php
                                $discPct = round((($product->price - $product->final_price) / $product->price) * 100);
                            @endphp
                            <span class="qw-discount-badge">{{ $discPct }}% OFF</span>
                        @endif

                        <a href="{{ route('product.detail', $product->slug) }}" class="text-decoration-none">
                            <div class="qw-product-img-wrapper">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="qw-product-img" loading="lazy">
                                @if($product->total_stock <= 0)
                                    <div class="qw-out-of-stock-overlay">
                                        <span class="qw-out-of-stock-badge">OUT OF STOCK</span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small text-uppercase font-bold" style="font-size: 0.75rem;">{{ $product->category->name }}</span>
                                <button type="button" onclick="shareProductLink('{{ route('product.detail', $product->slug) }}', '{{ addslashes($product->name) }}')" class="btn btn-link text-muted p-0 border-0" title="Share Product Link">
                                    <i class="fa-solid fa-share-nodes text-gold"></i>
                                </button>
                            </div>
                            <h6 class="font-serif fw-bold text-dark mb-2 text-truncate" title="{{ $product->name }}">
                                <a href="{{ route('product.detail', $product->slug) }}" class="text-dark text-decoration-none">{{ $product->name }}</a>
                            </h6>

                            <div class="mt-auto d-flex align-items-baseline gap-2 mb-3">
                                <span class="fs-5 fw-bold text-gold">₹{{ number_format($product->final_price, 2) }}</span>
                                @if($product->discount_type !== 'none' && $product->price > $product->final_price)
                                    <span class="text-muted text-decoration-line-through small">₹{{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>

                            <div class="d-grid gap-2">
                                @if($product->total_stock <= 0)
                                    <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-secondary btn-sm opacity-75">OUT OF STOCK</a>
                                @else
                                    <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-qw-outline btn-sm btn-select-buy">SELECT SIZE & BUY</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa-solid fa-shirt text-muted fs-1 mb-3"></i>
                    <h5>No products available right now.</h5>
                    <p class="text-muted">Check back soon for new arrivals!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

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
    function shareProductLink(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title + ' - ' + @js($siteName),
                text: 'Check out ' + title + ' on ' + @js($siteName) + '!',
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
