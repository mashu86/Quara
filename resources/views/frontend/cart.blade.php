@extends('layouts.app')

@section('title', 'Shopping Cart - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('styles')
<style>
    .cart-card-list { display: grid; grid-template-columns: minmax(0, 1fr); gap: .55rem; }
    .cart-card-list .cart-mobile-card { padding: .75rem; margin-bottom: 0 !important; }
    .cart-mobile-image { width: 80px; height: 102px; object-fit: cover; }
    .cart-mobile-title { font-size: .88rem; line-height: 1.25; }
    .cart-mobile-meta { font-size: .76rem; }
    .cart-mobile-actions { margin: .65rem -.75rem -.75rem; padding: .55rem .75rem; border-top: 1px solid #eadca8 !important; }
    .cart-mobile-actions form { flex: 1 1 0; min-width: 0; }
    .cart-mobile-actions .btn { min-height: 38px; font-size: .76rem; background: #fff; border: 1px solid #c6a23a; color: #9a7615; transition: background-color .18s ease, color .18s ease, box-shadow .18s ease; }
    .cart-mobile-actions .btn:hover, .cart-mobile-actions .btn:focus-visible, .cart-mobile-actions .btn:active { background: #c6a23a; border-color: #c6a23a; color: #fff; box-shadow: 0 3px 10px rgba(154, 118, 21, .2); }
    .cart-mobile-actions .btn .fa-bolt { color: inherit !important; }
    @media (min-width: 992px) {
        .cart-card-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
    }
    @media (max-width: 767.98px) {
        .cart-items-card { padding: .65rem !important; }
        .cart-items-table { font-size: .72rem; }
        .cart-items-table th, .cart-items-table td { padding: .45rem .35rem; }
        .cart-product-name { font-size: .75rem; line-height: 1.2; }
        .cart-items-table .badge { font-size: .62rem !important; }
        .cart-card-list { gap: .45rem; }
        .cart-card-list .cart-mobile-card { padding: .55rem; }
        .cart-mobile-card > .d-flex { gap: .55rem !important; }
        .cart-mobile-image { width: 68px; height: 86px; object-fit: cover; }
        .cart-mobile-title { font-size: .78rem; line-height: 1.2; margin-bottom: .3rem !important; }
        .cart-mobile-meta { font-size: .68rem; margin-bottom: .2rem !important; }
        .cart-mobile-actions { margin: .55rem -.55rem -.55rem; padding: .45rem .55rem; border-top: 1px solid #eadca8 !important; }
        .cart-mobile-actions .btn { min-height: 34px; font-size: .7rem; }
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-cart-remove]');
    if (!form) return;

    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    if (button) {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Could not remove this item. Please try again.');

        document.querySelectorAll('[data-cart-items-count]').forEach((badge) => {
            badge.textContent = result.cart_items_count;
            badge.classList.toggle('d-none', result.cart_items_count === 0);
        });

        const main = document.getElementById('cartMainContent');
        if (result.cart_items_count === 0 && main) {
            main.innerHTML = `<div class="col-12"><div class="bg-white p-4 rounded-4 shadow-sm border text-center my-4">
                <i class="fa-solid fa-bag-shopping text-muted display-4 mb-3"></i>
                <h5 class="font-serif fw-bold">Your cart is currently empty</h5>
                <p class="text-muted small mb-3">Looks like you haven't added any trendy pieces to your cart yet.</p>
                <a href="{{ route('shop') }}" class="btn btn-qw-gold btn-sm px-3 py-1-5 rounded-pill" style="font-size: .78rem;">CONTINUE SHOPPING</a>
            </div></div>`;
            document.getElementById('cartStockNotice')?.remove();
        } else {
            const cardList = document.querySelector('.cart-card-list');
            if (cardList && result.cart_cards_html) cardList.outerHTML = result.cart_cards_html;
            const summary = document.querySelector('.cart-summary-card');
            if (summary && result.summary_html) summary.outerHTML = result.summary_html;
            if (result.stock_validation?.valid) {
                document.getElementById('cartStockNotice')?.remove();
            }
        }
    } catch (error) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger small py-2 mb-3';
        alert.textContent = error.message || 'Could not remove this item. Please try again.';
        document.getElementById('cartMainContent')?.prepend(alert);
    } finally {
        if (button?.isConnected) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        }
    }
});
</script>
@endsection

@section('content')
<div class="container py-4">
    <h5 class="font-serif fw-bold fs-5 mb-3"><i class="fa-solid fa-bag-shopping text-gold me-2"></i> YOUR SHOPPING CART</h5>

    @if(empty($cart) || count($cart) === 0)
        <div class="bg-white p-4 rounded-4 shadow-sm border text-center my-4">
            <i class="fa-solid fa-bag-shopping text-muted display-4 mb-3"></i>
            <h5 class="font-serif fw-bold">Your cart is currently empty</h5>
            <p class="text-muted small mb-3">Looks like you haven't added any trendy pieces to your cart yet.</p>
            <a href="{{ route('shop') }}" class="btn btn-qw-gold btn-sm px-3 py-1-5 rounded-pill" style="font-size: 0.78rem;">CONTINUE SHOPPING</a>
        </div>
    @else
        @if(!$stockValidation['valid'])
            <div id="cartStockNotice" class="alert alert-warning rounded-3 shadow-sm mb-3 py-2 px-3 small">
                <h6 class="fw-bold mb-1 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> Stock Availability Notice</h6>
                <ul class="mb-0 ps-3 small">
                    @foreach($stockValidation['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3" id="cartMainContent">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                @include('frontend.partials.cart_cards', ['cart' => $cart])
                <div class="cart-items-card d-none bg-white p-3 p-md-4 rounded-4 shadow-sm border mb-3">
                    <div class="table-responsive">
                        <table class="cart-items-table table align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($cart as $key => $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 44px; height: 56px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h6 class="cart-product-name font-serif fw-bold mb-0 small">
                                                        <a href="{{ route('product.detail', $item['slug']) }}" class="text-dark text-decoration-none">{{ $item['name'] }}</a>
                                                    </h6>
                                                    @if(!empty($item['is_combo_offer']))
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">👑 Offer Combo Item</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>@if(!empty($item['size']))<span class="badge bg-dark px-2 py-1" style="font-size: 0.7rem;">{{ $item['size'] }}</span>@endif</td>
                                        <td>
                                            @if(!empty($item['is_combo_offer']))
                                                <span class="badge bg-warning text-dark d-block mb-1" style="font-size: 0.62rem;">Combo Item</span>
                                            @endif
                                                <span class="fw-bold">₹{{ number_format($item['final_price'], 2) }}</span>
                                                @if($item['discount_amount'] > 0)
                                                    <div class="text-muted text-decoration-line-through style-small" style="font-size: 0.7rem;">₹{{ number_format($item['price'], 2) }}</div>
                                                @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $item['quantity'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($item['is_combo_offer']))
                                                <span class="badge bg-warning text-dark d-block mb-1" style="font-size: 0.62rem;">Combo</span>
                                            @endif
                                                <span class="fw-bold text-gold fs-6">₹{{ number_format($item['subtotal'], 2) }}</span>
                                        </td>
                                        <td class="text-end d-none d-lg-table-cell">
                                            <form action="{{ route('cart.remove', $key) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Remove item"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="{{ route('shop') }}" class="btn btn-qw-outline btn-sm rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;"><i class="fa-solid fa-arrow-left me-1"></i> Continue Shopping</a>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                @include('frontend.partials.cart_summary', ['summary' => $summary, 'stockValidation' => $stockValidation])
            </div>
        </div>
    @endif
</div>
@endsection
