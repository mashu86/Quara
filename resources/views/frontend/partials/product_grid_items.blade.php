@forelse($products as $product)
    <div class="col-6 col-md-4 product-item-col animate__animated animate__fadeIn">
        <div class="qw-product-card h-100 d-flex flex-column">
            @if($product->discount_type !== 'none' && $product->price > 0)
                @php
                    $discPct = round((($product->price - $product->final_price) / $product->price) * 100);
                @endphp
                <span class="qw-discount-badge">{{ $discPct }}% OFF</span>
            @endif

            <a href="{{ route('product.detail', $product->slug) }}">
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
                    <span class="text-muted small text-uppercase font-bold" style="font-size: 0.75rem;">{{ $product->category ? $product->category->name : 'Fashion' }}</span>
                    <button type="button" onclick="shareProductLink('{{ route('product.detail', $product->slug) }}', '{{ addslashes($product->name) }}')" class="btn btn-link text-muted p-0 border-0" title="Share Product">
                        <i class="fa-solid fa-share-nodes text-gold"></i>
                    </button>
                </div>
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
                <div class="mb-3 d-flex flex-wrap gap-1 align-items-center">
                    @foreach($product->sizes as $pSize)
                        <span class="badge {{ $pSize->stock > 0 ? 'bg-light text-dark border' : 'bg-secondary text-white opacity-75' }} px-2 py-1" style="font-size: 0.72rem;">
                            {{ $pSize->size }}
                        </span>
                    @endforeach
                </div>

                <div class="d-grid">
                    @if($product->total_stock <= 0)
                        <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-secondary btn-sm opacity-75 qw-btn-card">OUT OF STOCK</a>
                    @else
                        <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-qw-outline btn-sm qw-btn-card">VIEW DETAILS</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    @if(($isAjax ?? false))
        <!-- No more items to load -->
    @else
        <div class="col-12 py-5 text-center bg-white rounded-4 border">
            <i class="fa-solid fa-magnifying-glass text-muted fs-1 mb-3"></i>
            <h5>No products found matching your criteria.</h5>
            <p class="text-muted small mb-3">Try adjusting your search terms or filters.</p>
            <a href="{{ url()->current() }}" class="btn btn-qw-gold rounded-pill px-4 btn-sm">RESET ALL FILTERS</a>
        </div>
    @endif
@endforelse
