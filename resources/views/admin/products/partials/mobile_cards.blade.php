@forelse($products as $product)
    @php
        $totalStock = $product->sizes->sum('stock');
        $categoriesForCard = $product->categories->isNotEmpty() ? $product->categories : collect([$product->category])->filter();
        $isBooked = $product->is_out_of_stock && (!empty($product->booked_by) || $product->booking_type === 'business_whatsapp');
        $bookedQty = (int) $product->bookings->sum(fn ($booking) => $booking->remaining_quantity);
        $imgSrc = $product->primary_image_url;
    @endphp
    <div class="col">
        <article class="card mobile-product-card h-100 overflow-hidden">
            <div class="mobile-product-image-wrap position-relative" role="button"
                 onclick="openProductPreview('{{ addslashes($imgSrc) }}', '{{ addslashes($product->name) }}', '₹{{ number_format($product->final_price, 0) }}', {{ json_encode($product->sizes) }}, '{{ addslashes($product->product_code) }}', 0, {{ json_encode($product->image_urls) }})"
                 title="Click to preview product image">
                <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="mobile-product-image" loading="lazy">
                @if($isBooked)
                    <span class="badge bg-dark text-warning mobile-product-ribbon"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Booked</span>
                @elseif($totalStock > 0 && $bookedQty >= $totalStock)
                    <span class="badge bg-dark text-warning mobile-product-ribbon"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>All Booked</span>
                @elseif($product->is_out_of_stock || $totalStock <= 0)
                    <span class="badge bg-danger mobile-product-ribbon">Sold Out</span>
                @endif
                @if(!empty($product->combo_category_id))
                    <span class="badge bg-warning text-dark mobile-product-offer"><i class="fa-solid fa-star" aria-label="Offer"></i></span>
                @endif
                <span class="position-absolute bottom-0 end-0 m-1 badge rounded-circle bg-dark bg-opacity-75 p-1.5" aria-hidden="true">
                    <i class="fa-solid fa-eye text-white" style="font-size: 0.7rem;"></i>
                </span>
            </div>

            <div class="card-body p-2 p-sm-3 d-flex flex-column">
                <h6 class="mobile-product-name fw-bold text-dark mb-2" title="{{ $product->name }}">{{ $product->name }}</h6>
                @if(filled($product->product_code))
                    <div class="mb-2">
                        <span class="badge bg-light text-dark border font-monospace" title="Product code">{{ $product->product_code }}</span>
                    </div>
                @endif

                <div class="d-flex align-items-baseline gap-1 flex-wrap mb-2">
                    <span class="fw-bold text-gold">₹{{ number_format($product->final_price, 2) }}</span>
                    @if($product->price > $product->final_price)
                        <span class="small text-muted text-decoration-line-through">₹{{ number_format($product->price, 2) }}</span>
                        @if($product->discount_type === 'percentage')
                            <span class="badge bg-danger-subtle text-danger">{{ (int) $product->discount_value }}% off</span>
                        @elseif($product->discount_type === 'fixed')
                            <span class="badge bg-danger-subtle text-danger">₹{{ number_format($product->discount_value, 0) }} off</span>
                        @endif
                    @endif
                </div>

                @if($categoriesForCard->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 mb-2 mobile-product-categories">
                        @foreach($categoriesForCard as $category)
                            <span class="badge bg-light text-secondary border mobile-product-category">{{ $category->name }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mobile-product-stock mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted fw-semibold">Stock</span>
                        <span class="small fw-bold {{ $totalStock > 0 ? 'text-success' : 'text-danger' }}">{{ $totalStock }} pcs</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($product->sizes as $productSize)
                            @php $reservedQty = (int) $product->bookings->where('product_size_id', $productSize->id)->sum(fn ($booking) => $booking->remaining_quantity); @endphp
                            <span class="badge {{ $productSize->stock > $reservedQty ? 'bg-light text-dark border' : ($reservedQty > 0 ? 'bg-warning text-dark' : 'bg-danger-subtle text-danger') }}" title="Size {{ $productSize->size }}; {{ $reservedQty }} reserved">
                                {{ $productSize->size }}: {{ $productSize->stock }}
                                @if($reservedQty > 0)<span class="opacity-75">({{ $reservedQty }} booked)</span>@endif
                            </span>
                        @empty
                            <span class="small text-muted">No size stock</span>
                        @endforelse
                    </div>
                </div>

                <div class="mb-2 mobile-booking-block">
                    @include('admin.products.partials.booking_actions')
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                    <div class="form-check form-switch mb-0 d-inline-flex align-items-center gap-1" title="{{ ucfirst($product->status) }}">
                        <input class="form-check-input product-status-toggle" type="checkbox" role="switch"
                               id="mobileProductStatusToggle_{{ $product->id }}"
                               data-product-id="{{ $product->id }}"
                               data-url="{{ route('admin.products.toggle-status', $product->id) }}"
                               {{ $product->status === 'active' ? 'checked' : '' }}
                               style="cursor: pointer; width: 2.3em; height: 1.2em;">
                        <label class="form-check-label small fw-semibold" for="mobileProductStatusToggle_{{ $product->id }}">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark mobile-product-action" aria-label="Edit {{ $product->name }}" title="Edit product">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this product permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger mobile-product-action" aria-label="Delete {{ $product->name }}" title="Delete product">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </article>
    </div>
@empty
    <div class="col-12">
        <div class="text-center text-muted bg-white rounded-4 border py-5">No products found matching filters.</div>
    </div>
@endforelse
