@forelse($products as $product)
    @php
        $hasDiscount = ($product->discount_type !== 'none' && $product->price > $product->final_price);
        $discPct = $hasDiscount ? round((($product->price - $product->final_price) / $product->price) * 100) : 0;
        $finalFormatted = $product->final_price == floor($product->final_price) ? number_format($product->final_price, 0) : number_format($product->final_price, 2);
        $origFormatted = $product->price == floor($product->price) ? number_format($product->price, 0) : number_format($product->price, 2);
    @endphp
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 product-item-col animate__animated animate__fadeIn">
        <a href="{{ route('product.detail', $product->slug) }}" class="qw-product-card h-100 d-flex flex-column shadow-sm rounded-4 overflow-hidden border text-decoration-none text-dark d-block">
            <!-- Image Section with Overlay Elements -->
            <div class="qw-product-img-wrapper position-relative">
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="qw-product-img" loading="lazy">
                
                @if($hasDiscount)
                    <span class="badge bg-danger text-white position-absolute top-0 start-0 m-1.5 m-sm-2 px-2 py-0.5 rounded-pill shadow-sm fw-bold qw-discount-badge" style="font-size: 0.65rem; z-index: 2; letter-spacing: 0.3px;">
                        {{ $discPct }}% OFF
                    </span>
                @endif

                <button type="button" onclick="event.stopPropagation(); event.preventDefault(); shareProductLink('{{ route('product.detail', $product->slug) }}', '{{ addslashes($product->name) }}')" 
                        class="btn btn-light rounded-2 position-absolute top-0 end-0 m-1.5 m-sm-2 me-2 me-sm-2.5 p-0 shadow-sm border-0 d-flex align-items-center justify-content-center qw-share-btn-floating" 
                        style="width: 28px; height: 28px; z-index: 3; background: rgba(255,255,255,0.9); color: #111; backdrop-filter: blur(6px); transition: all 0.2s ease;" 
                        title="Share Product">
                    <i class="fa-solid fa-share-nodes" style="font-size: 0.70rem;"></i>
                </button>

                @if($product->total_stock <= 0)
                    <div class="qw-out-of-stock-overlay">
                        <span class="qw-out-of-stock-badge">SOLD OUT</span>
                    </div>
                @endif
            </div>

            <!-- Card Body -->
            <div class="qw-card-body p-2 p-sm-3 d-flex flex-column flex-grow-1">
                <!-- Product Name Title (Single Line with Ellipsis for Long Names) -->
                <h6 class="qw-product-title mb-1.5" title="{{ $product->name }}">
                    <span class="text-dark fw-bold text-truncate d-block">{{ Str::limit($product->name, 22, '...') }}</span>
                </h6>

                <!-- Price Row (Selling Price + Red Cut Price Side-by-Side with small gap) -->
                <div class="mt-auto d-flex align-items-baseline gap-1.5 mb-1.5">
                    <span class="qw-product-price">₹{{ $finalFormatted }}</span>
                    @if($hasDiscount)
                        <span class="qw-cut-price">₹{{ $origFormatted }}</span>
                    @endif
                </div>

                <!-- Available Sizes & Measurements -->
                <div class="mb-0">
                    <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                        @foreach($product->sizes as $pSize)
                            <span class="badge {{ $pSize->stock > 0 ? 'bg-light text-dark border-0' : 'bg-secondary text-white opacity-50' }} px-2 py-1 rounded-2 fw-bold" 
                                  style="font-size: 0.63rem; background-color: #f3f4f6 !important; color: #222 !important;"
                                  title="Size {{ $pSize->size }}">
                                <span>{{ $pSize->size }}</span>
                            </span>
                        @endforeach
                    </div>

                    @php
                        $allMeasurements = [];
                        foreach($product->sizes as $pSize) {
                            if($pSize->stock > 0) {
                                $mParts = [];
                                if(!empty($pSize->chest)) $mParts[] = 'C:'.$pSize->chest.'"';
                                if(!empty($pSize->waist)) $mParts[] = 'W:'.$pSize->waist.'"';
                                if(!empty($pSize->length)) $mParts[] = 'L:'.$pSize->length.'"';
                                if(count($mParts) > 0) {
                                    $allMeasurements[] = count($product->sizes) > 1 
                                        ? $pSize->size.': '.implode(' ', $mParts) 
                                        : implode(' • ', $mParts);
                                }
                            }
                        }
                    @endphp

                    @if(count($allMeasurements) > 0)
                        <div class="text-muted fw-semibold w-100 mt-1.5 text-truncate" style="font-size: 0.63rem; line-height: 1.3;" title="{{ implode(' | ', $allMeasurements) }}">
                            <i class="fa-solid fa-ruler text-warning me-1"></i> {{ implode(' • ', $allMeasurements) }}
                        </div>
                    @endif
                </div>
            </div>
        </a>
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
