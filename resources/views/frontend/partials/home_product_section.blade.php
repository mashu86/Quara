@php
    $activeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('category') ? 1 : 0)
        + (request()->filled('min_price') ? 1 : 0)
        + (request()->filled('max_price') ? 1 : 0)
        + (request()->filled('size') ? 1 : 0)
        + (request()->filled('stock') ? 1 : 0);
@endphp

<section class="py-4 py-md-5 bg-white border-top border-bottom" id="all-products-section">
    <div class="container">
        <!-- Section Header & Sorting -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
            <div>
                <span class="text-gold text-uppercase fw-bold small">TRENDING NOW</span>
                <h2 class="font-serif display-6 fw-bold mb-1 fs-3 fs-md-2">
                    @if($currentCategory)
                        {{ $currentCategory->name }}
                    @elseif(request()->filled('search'))
                        Search Results for "{{ request()->search }}"
                    @else
                        OUR COLLECTION
                    @endif
                </h2>
                <p id="productCountText" class="text-muted small mb-0">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} trendy pieces at Quara Wardrobe</p>
            </div>

            <div class="d-flex align-items-center gap-3 mt-3 mt-md-0">
                <!-- Sorting dropdown -->
                <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
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
        </div>

        <style>
            .filter-trigger-btn {
                width: 100%;
            }
            @media (min-width: 768px) {
                .filter-trigger-btn {
                    width: auto !important;
                }
            }
        </style>

        <!-- Universal Filter Button Bar (Right-aligned on Desktop, White & Gold style) -->
        <div class="d-flex justify-content-end mb-4">
            <div class="d-flex gap-2 w-100 justify-content-end align-items-center">
                <button type="button" class="btn rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-2 filter-trigger-btn"
                        data-bs-toggle="modal" data-bs-target="#homeFilterModal"
                        style="background-color: #ffffff; border: 2px solid #D4AF37; color: #D4AF37; transition: all 0.2s ease;">
                    <i class="fa-solid fa-sliders" style="color: #D4AF37; font-size: 1.05rem;"></i>
                    <span class="fw-bold" style="color: #D4AF37; letter-spacing: 0.3px;">Filter & Search</span>
                    @if($activeFilterCount > 0)
                        <span class="badge rounded-pill text-white ms-1" style="background-color: #D4AF37;">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                @if($activeFilterCount > 0)
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-3 d-flex align-items-center" title="Clear Filters" style="border-color: #ddd;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Universal Filter Modal (Pop-up on all screens) -->
        <div class="modal fade" id="homeFilterModal" tabindex="-1" aria-labelledby="homeFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                        <h5 class="modal-title font-serif fw-bold" id="homeFilterModalLabel">
                            <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Products
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('home') }}" method="GET">
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
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center py-1-5 fw-semibold" style="font-size: 0.8rem; padding-top: 6px; padding-bottom: 6px;">
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
</section>
