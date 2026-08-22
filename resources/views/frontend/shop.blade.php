@extends('layouts.app')

@section('title', 'Shop All - QUARA WALDROP Fashion Store')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Header -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shop</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="font-serif fw-bold display-6 mb-1">
                @if(request()->filled('category'))
                    {{ ucfirst(str_replace('-', ' ', request()->category)) }}
                @else
                    ALL PRODUCTS
                @endif
            </h2>
            <p class="text-muted small mb-0">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} trendy pieces</p>
        </div>

        <!-- Sorting dropdown -->
        <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2 mt-3 mt-md-0">
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

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="bg-white p-4 rounded-4 shadow-sm border">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-serif fw-bold mb-0"><i class="fa-solid fa-filter text-gold me-2"></i> Filters</h5>
                    @if(count(request()->all()) > 0)
                        <a href="{{ route('shop') }}" class="btn btn-link btn-sm text-danger text-decoration-none p-0">Clear All</a>
                    @endif
                </div>

                <form action="{{ route('shop') }}" method="GET">
                    <!-- Search Input -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Name or keyword..." value="{{ request()->search }}">
                            <button type="submit" class="btn btn-qw-gold rounded-end-pill"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Category</label>
                        <select name="category" class="form-select form-select-sm rounded-3">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request()->category == $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Price Range (₹)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm rounded-3" placeholder="Min" value="{{ request()->min_price }}">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm rounded-3" placeholder="Max" value="{{ request()->max_price }}">
                            </div>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Size</label>
                        <select name="size" class="form-select form-select-sm rounded-3">
                            <option value="">All Sizes</option>
                            @foreach($allSizes as $sz)
                                <option value="{{ $sz }}" {{ request()->size == $sz ? 'selected' : '' }}>Size {{ $sz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stock Availability -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Stock Status</label>
                        <select name="stock" class="form-select form-select-sm rounded-3">
                            <option value="">All Products</option>
                            <option value="in_stock" {{ request()->stock == 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                            <option value="out_of_stock" {{ request()->stock == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-qw-gold w-100 rounded-pill btn-sm">APPLY FILTERS</button>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-6 col-md-4">
                        <div class="qw-product-card h-100 d-flex flex-column">
                            @if($product->discount_type !== 'none' && $product->price > 0)
                                @php
                                    $discPct = round((($product->price - $product->final_price) / $product->price) * 100);
                                @endphp
                                <span class="qw-discount-badge">{{ $discPct }}% OFF</span>
                            @endif

                            <a href="{{ route('product.detail', $product->slug) }}">
                                <div class="qw-product-img-wrapper">
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="qw-product-img">
                                </div>
                            </a>

                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <span class="text-muted small text-uppercase font-bold mb-1" style="font-size: 0.75rem;">{{ $product->category->name }}</span>
                                <h6 class="font-serif fw-bold text-dark mb-2 text-truncate" title="{{ $product->name }}">
                                    <a href="{{ route('product.detail', $product->slug) }}" class="text-dark text-decoration-none">{{ $product->name }}</a>
                                </h6>

                                <div class="mt-auto d-flex align-items-baseline gap-2 mb-2">
                                    <span class="fs-5 fw-bold text-gold">₹{{ number_format($product->final_price, 2) }}</span>
                                    @if($product->discount_type !== 'none' && $product->price > $product->final_price)
                                        <span class="text-muted text-decoration-line-through small">₹{{ number_format($product->price, 2) }}</span>
                                    @endif
                                </div>

                                <!-- Available Sizes -->
                                <div class="mb-3">
                                    @foreach($product->sizes as $pSize)
                                        <span class="badge {{ $pSize->stock > 0 ? 'bg-light text-dark border' : 'bg-secondary text-white' }} small me-1">
                                            {{ $pSize->size }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="d-grid">
                                    <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-qw-outline btn-sm">VIEW DETAILS</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center bg-white rounded-4 border">
                        <i class="fa-solid fa-magnifying-glass text-muted fs-1 mb-3"></i>
                        <h5>No products found matching your criteria.</h5>
                        <p class="text-muted small mb-3">Try adjusting your search terms or filters.</p>
                        <a href="{{ route('shop') }}" class="btn btn-qw-gold rounded-pill px-4 btn-sm">RESET ALL FILTERS</a>
                    </div>
                @endforelse
            </div>

            <!-- Server-Side Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
