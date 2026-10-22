@forelse($products as $product)
    @php
        $totalStock = $product->sizes->sum('stock');
        $categoriesForCard = $product->categories->isNotEmpty() ? $product->categories : collect([$product->category])->filter();
        $isBooked = $product->is_out_of_stock && (!empty($product->booked_by) || $product->booking_type === 'business_whatsapp');
    @endphp
    <div class="col">
        <article class="card mobile-product-card h-100 overflow-hidden">
            <div class="mobile-product-image-wrap">
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="mobile-product-image" loading="lazy">
                @if($isBooked)
                    <span class="badge bg-dark text-warning mobile-product-ribbon"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Booked</span>
                @elseif($product->is_out_of_stock || $totalStock <= 0)
                    <span class="badge bg-danger mobile-product-ribbon">Sold Out</span>
                @endif
                @if(!empty($product->combo_category_id))
                    <span class="badge bg-warning text-dark mobile-product-offer"><i class="fa-solid fa-star" aria-label="Offer"></i></span>
                @endif
            </div>

            <div class="card-body p-2 p-sm-3 d-flex flex-column">
                <h6 class="mobile-product-name fw-bold text-dark mb-2" title="{{ $product->name }}">{{ $product->name }}</h6>

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
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach($categoriesForCard->take(2) as $category)
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
                        @forelse($product->sizes->take(4) as $productSize)
                            <span class="badge {{ $productSize->stock > 0 ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }}">{{ $productSize->size }}: {{ $productSize->stock }}</span>
                        @empty
                            <span class="small text-muted">No size stock</span>
                        @endforelse
                        @if($product->sizes->count() > 4)
                            <span class="badge bg-light text-secondary border">+{{ $product->sizes->count() - 4 }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                    <span class="badge rounded-pill {{ $product->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        <i class="fa-solid {{ $product->status === 'active' ? 'fa-circle-check' : 'fa-circle-pause' }} me-1" aria-hidden="true"></i>{{ ucfirst($product->status) }}
                    </span>
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
