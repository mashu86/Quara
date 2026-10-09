<div class="cart-card-list mb-3">
    @foreach($cart as $key => $item)
        <article class="cart-mobile-card bg-white rounded-4 shadow-sm border mb-2">
            <div class="d-flex gap-3">
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="cart-mobile-image rounded-3 border flex-shrink-0">
                <div class="flex-grow-1 min-w-0">
                    <h6 class="cart-mobile-title fw-bold mb-2">
                        <a href="{{ route('product.detail', $item['slug']) }}" class="text-dark text-decoration-none">{{ $item['name'] }}</a>
                    </h6>
                    @if(!empty($item['is_combo_offer']))
                        <div class="mb-1"><span class="badge bg-warning text-dark">Combo offer item</span></div>
                    @endif
                    <div class="cart-mobile-meta text-muted mb-1">Size: <strong class="text-dark">{{ $item['size'] ?: 'Free size' }}</strong></div>
                    <div class="cart-mobile-meta mb-2">Price: <strong class="text-dark">&#8377;{{ number_format($item['final_price'], 2) }}</strong>
                        @if($item['discount_amount'] > 0)
                            <span class="text-muted text-decoration-line-through ms-1">&#8377;{{ number_format($item['price'], 2) }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 cart-mobile-meta">
                        <span class="text-muted">Qty</span>
                        <span class="badge bg-light text-dark border px-2 py-1" aria-label="Quantity {{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                        <span class="ms-auto fw-bold text-gold">&#8377;{{ number_format($item['subtotal'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="cart-mobile-actions d-flex gap-2 mt-3 pt-2 border-top">
                <form action="{{ route('cart.remove', $key) }}" method="POST" class="flex-fill" data-cart-remove>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn rounded-3 w-100 fw-semibold" title="Remove {{ $item['name'] }} from cart" aria-label="Remove {{ $item['name'] }} from cart">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                    </button>
                </form>
                <form action="{{ route('cart.buy_now') }}" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                    <input type="hidden" name="size" value="{{ $item['size'] }}">
                    <input type="hidden" name="quantity" value="{{ $item['quantity'] }}">
                    <button type="submit" class="btn rounded-3 w-100 fw-bold">
                        <i class="fa-solid fa-bolt me-1" aria-hidden="true"></i>Buy Now
                    </button>
                </form>
            </div>
        </article>
    @endforeach
</div>
