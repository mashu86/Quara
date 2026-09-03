@extends('layouts.app')

@section('title', $seoTitle ?? ('Shop All Ladies Fashion - ' . $siteName))
@section('meta_description', $seoDescription ?? ('Browse the complete collection of stylish western wear, Korean tops, and dresses at ' . $siteName . ' online shop.'))
@section('canonical_url', $canonicalUrl ?? route('shop'))

@section('json_ld')
<script type="application/ld+json">
{
  "\u0040context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ route('home') }}"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "{{ $currentCategory ? $currentCategory->name : 'Shop' }}",
      "item": "{{ $canonicalUrl ?? route('shop') }}"
    }
  ]
}
</script>
@endsection

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Header -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            @if($currentCategory)
                <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory->name }}</li>
            @else
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            @endif
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="font-serif fw-bold display-6 mb-1 fs-2">
                @if($currentCategory)
                    {{ $currentCategory->name }}
                @elseif(request()->filled('search'))
                    Search Results for "{{ request()->search }}"
                @else
                    ALL PRODUCTS
                @endif
            </h1>
            <p class="text-muted small mb-0">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} trendy pieces at Quara Wardrobe</p>
        </div>

        <!-- Sorting dropdown -->
        <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2 mt-3 mt-md-0 mb-2 mb-md-0">
            @foreach(request()->except(['sort', 'page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <label for="sort" class="small fw-semibold text-nowrap">Sort By:</label>
            <select name="sort" id="sort" class="form-select form-select-sm rounded-pill shadow-sm" onchange="this.form.submit()">
                <option value="newest" {{ request()->sort == 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="price_low" {{ request()->sort == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request()->sort == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="oldest" {{ request()->sort == 'oldest' ? 'selected' : '' }}>Oldest First</option>
            </select>
        </form>
    </div>

@php
    $activeShopFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('category') ? 1 : 0)
        + (request()->filled('min_price') ? 1 : 0)
        + (request()->filled('max_price') ? 1 : 0)
        + (request()->filled('size') ? 1 : 0)
        + (request()->filled('stock') ? 1 : 0);
@endphp

    <!-- Universal Filter Button Bar (Mobile & Desktop) -->
    <div class="mt-3 mb-4">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#shopFilterModal">
                <i class="fa-solid fa-sliders text-warning"></i>
                <span class="fw-semibold">Filter & Search</span>
                @if($activeShopFilterCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $activeShopFilterCount }}</span>
                @endif
            </button>
            @if($activeShopFilterCount > 0)
                <a href="{{ route('shop') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Universal Filter Modal (Pop-up on all screens) -->
    <div class="modal fade" id="shopFilterModal" tabindex="-1" aria-labelledby="shopFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                    <h5 class="modal-title font-serif fw-bold" id="shopFilterModalLabel">
                        <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Products
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('shop') }}" method="GET">
                    <div class="modal-body p-4">
                        <!-- Search Input -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Search Keyword</label>
                            <input type="text" name="search" class="form-control rounded-3" placeholder="Name or keyword..." value="{{ request()->search }}">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Category</label>
                            <select name="category" class="form-select rounded-3">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request()->category == $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Price Range (₹)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control rounded-3" placeholder="Min Price" value="{{ request()->min_price }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control rounded-3" placeholder="Max Price" value="{{ request()->max_price }}">
                                </div>
                            </div>
                        </div>

                        <!-- Size Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Size</label>
                            <select name="size" class="form-select rounded-3">
                                <option value="">All Sizes</option>
                                @foreach($allSizes as $sz)
                                    <option value="{{ $sz }}" {{ request()->size == $sz ? 'selected' : '' }}>Size {{ $sz }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Availability -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Stock Status</label>
                            <select name="stock" class="form-select rounded-3">
                                <option value="">All Products</option>
                                <option value="in_stock" {{ request()->stock == 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                                <option value="out_of_stock" {{ request()->stock == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>

                        <!-- Sort Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-dark">Sort By</label>
                            <select name="sort" class="form-select rounded-3">
                                <option value="newest" {{ request()->sort == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request()->sort == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request()->sort == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="oldest" {{ request()->sort == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2">
                        <div class="d-flex w-100 gap-2">
                            <a href="{{ route('shop') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center py-1-5 fw-semibold" style="font-size: 0.8rem; padding-top: 6px; padding-bottom: 6px;">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-qw-gold btn-sm rounded-pill flex-fill py-1-5 fw-bold" style="font-size: 0.8rem; padding-top: 6px; padding-bottom: 6px;">
                                <i class="fa-solid fa-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Product Grid (Full Width col-12) -->
        <div class="col-12">
            <div class="row g-4" id="productGridContainer">
                @include('frontend.partials.product_grid_items')
            </div>

            <!-- Modern Infinite Scroll Container -->
            <div id="infiniteScrollContainer" class="mt-4 text-center">
                <div id="infiniteScrollLoading" class="d-none py-3">
                    <div class="spinner-border text-warning me-2" role="status" style="width: 2.2rem; height: 2.2rem; border-width: 3px; color: var(--qw-gold) !important;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="fw-bold text-dark small align-middle">Loading more products...</span>
                </div>

                @if($products->hasMorePages())
                    <div id="infiniteScrollSentinel" class="py-2"></div>
                    <button id="loadMoreBtn" type="button" onclick="fetchNextPage()" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-bold shadow-sm mb-3" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-arrow-down-short-wide me-1 text-gold"></i> Load More Products
                    </button>
                @endif

                <div id="noMoreProductsNotice" class="{{ $products->hasMorePages() ? 'd-none' : '' }} text-muted small py-3 opacity-75">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> You have viewed all {{ $products->total() }} products!
                </div>
            </div>
        </div>
    </div>
</div>
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
                const countTextEl = document.querySelector('.text-muted.small.mb-0');
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
