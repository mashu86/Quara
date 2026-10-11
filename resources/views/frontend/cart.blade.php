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
    .cart-item-select { appearance: none; -webkit-appearance: none; width: 1.45rem; height: 1.45rem; margin: 0; border: 2px solid #c6a23a; border-radius: .42rem; background: #fff; box-shadow: 0 1px 5px rgba(0,0,0,.2); cursor: pointer; position: absolute; z-index: 2; top: .4rem; left: .4rem; display: grid; place-content: center; }
    .cart-item-select:checked { background: #c6a23a; border-color: #c6a23a; }
    .cart-item-select:checked::after { content: ''; width: .42rem; height: .72rem; border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg) translate(-1px,-1px); }
    .cart-item-select:focus-visible { outline: 3px solid rgba(198,162,58,.35); outline-offset: 2px; }
    .cart-selection-bar { position: sticky; top: 76px; z-index: 20; background: #e9f7ee; color: #176c3a; border: 1px solid #72c58f; box-shadow: 0 4px 12px rgba(22,101,54,.12); }
    .cart-select-all { appearance: auto; position: static; width: 1.15rem; height: 1.15rem; box-shadow: none; accent-color: #198754; }
    .cart-select-all:checked { background: #198754; border-color: #198754; }
    .cart-select-all:checked::after { display: none; }
    .cart-item-image-wrap { position: relative; flex: 0 0 auto; }
    .cart-item-image-wrap img { display: block; border: 1px solid #c6a23a !important; }
    .cart-header-actions { flex: 0 0 auto; }
    .cart-header-actions form { margin: 0; }
    .cart-header-actions .btn { white-space: nowrap; }
    .cart-mobile-actions { margin: .65rem -.75rem -.75rem; padding: .55rem .75rem; border-top: 1px solid #eadca8 !important; }
    .cart-mobile-actions { display: flex; flex-direction: row; flex-wrap: nowrap; }
    .cart-mobile-actions form { flex: 1 1 0; min-width: 0; }
    .cart-mobile-actions .btn { min-height: 34px; padding: .3rem .4rem; font-size: .7rem; line-height: 1.15; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: .3rem; background: #fff; border: 1px solid #c6a23a; color: #9a7615; transition: background-color .18s ease, color .18s ease, box-shadow .18s ease; }
    .cart-mobile-actions .btn:hover, .cart-mobile-actions .btn:focus-visible, .cart-mobile-actions .btn:active { background: #c6a23a; border-color: #c6a23a; color: #fff; box-shadow: 0 3px 10px rgba(154, 118, 21, .2); }
    .cart-mobile-actions .btn .fa-bolt { color: inherit !important; }
    @media (min-width: 992px) {
        .cart-card-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
    }
    @media (max-width: 767.98px) {
        .cart-header-actions.cart-has-items { display: grid !important; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .5rem !important; width: 100%; }
        .cart-header-actions.cart-has-items > a, .cart-header-actions.cart-has-items > form, .cart-header-actions.cart-has-items > form .btn { width: 100%; min-width: 0; }
        .cart-header-actions .btn { padding: .4rem .45rem !important; font-size: .68rem !important; line-height: 1.1; }
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
        .cart-mobile-actions .btn { min-height: 32px; padding: .25rem .3rem; font-size: .64rem; }
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = () => [...document.querySelectorAll('[data-cart-select]')];
    const selectedKeys = () => [...new Set(checkboxes().filter((box) => box.checked).map((box) => box.value))];
    const deleteForm = document.getElementById('cartBulkDeleteForm');
    const deleteButton = document.getElementById('cartBulkDeleteButton');
    const deleteKeys = document.getElementById('cartBulkDeleteKeys');
    const allCheckbox = document.getElementById('cartSelectAll');
    let summaryRequestId = 0;
    const refreshSelectedSummary = (selected) => {
        if (!checkboxes().length) return;
        const requestId = ++summaryRequestId;
        const checkoutButton = () => document.querySelector('#cartCheckoutForm button[type="submit"]');
        const status = document.getElementById('cartSummaryCalculationStatus');
        const currentButton = checkoutButton();
        if (currentButton) {
            currentButton.disabled = true;
            currentButton.setAttribute('aria-busy', 'true');
        }
        if (status) {
            status.hidden = false;
            status.textContent = 'Updating order summary…';
        }
        const formData = new FormData();
        const csrf = deleteForm?.querySelector('input[name="_token"]')?.value;
        if (csrf) formData.append('_token', csrf);
        selected.forEach((key) => formData.append('cart_keys[]', key));

        fetch(@json(route('cart.summary_preview')), {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(async (response) => {
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to update the order summary.');
            if (requestId !== summaryRequestId) return;
            const currentSummary = document.querySelector('.cart-summary-card');
            if (currentSummary && result.summary_html) currentSummary.outerHTML = result.summary_html;
            const updatedButton = checkoutButton();
            if (updatedButton) {
                updatedButton.disabled = result.calculation_complete !== true;
                if (updatedButton.disabled) updatedButton.setAttribute('aria-busy', 'true');
                else updatedButton.removeAttribute('aria-busy');
            }
            const updatedStatus = document.getElementById('cartSummaryCalculationStatus');
            if (updatedStatus) {
                updatedStatus.hidden = result.calculation_complete === true;
                updatedStatus.textContent = result.calculation_complete === true
                    ? ''
                    : 'Order summary is still being updated.';
            }
            const notice = document.getElementById('cartSelectionNotice');
            const itemCount = new Set(checkboxes().map((box) => box.value)).size;
            if (notice) notice.textContent = selected.length === itemCount
                ? 'All products selected'
                : `${selected.length} item(s) selected${selected.length === 0 ? ' — checkout includes all items' : ''}`;
        }).catch(() => {
            if (requestId !== summaryRequestId) return;
            const button = checkoutButton();
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            }
            const latestStatus = document.getElementById('cartSummaryCalculationStatus');
            if (latestStatus) {
                latestStatus.hidden = false;
                latestStatus.textContent = 'Unable to update the order summary. Please try again.';
            }
        });
    };

    const syncSelection = () => {
        const selected = selectedKeys();
        const allKeys = [...new Set(checkboxes().map((box) => box.value))];
        checkboxes().forEach((box) => { box.checked = selected.includes(box.value); });
        if (allCheckbox) {
            allCheckbox.checked = allKeys.length > 0 && selected.length === allKeys.length;
            allCheckbox.indeterminate = selected.length > 0 && selected.length < allKeys.length;
        }
        const notice = document.getElementById('cartSelectionNotice');
        if (notice) notice.textContent = selected.length === allKeys.length
            ? 'All products selected'
            : `${selected.length} item(s) selected${selected.length === 0 ? ' — checkout includes all items' : ''}`;
        if (!deleteForm || !deleteButton) return;

        deleteButton.disabled = false;
        deleteForm.action = selected.length
            ? @json(route('cart.remove_selected'))
            : @json(route('cart.clear'));
        deleteButton.textContent = selected.length ? 'Delete selected items' : 'Remove all items';
        deleteKeys.replaceChildren();
        selected.forEach((key) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'cart_keys[]'; input.value = key;
            deleteKeys.appendChild(input);
        });
        refreshSelectedSummary(selected);
    };
    window.syncCartSelection = syncSelection;

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-cart-select-all]')) {
            checkboxes().forEach((box) => { box.checked = event.target.checked; });
            syncSelection();
            return;
        }
        if (!event.target.matches('[data-cart-select]')) return;
        const key = event.target.value;
        checkboxes().filter((box) => box.value === key).forEach((box) => { box.checked = event.target.checked; });
        syncSelection();
    });

    deleteForm?.addEventListener('submit', (event) => {
        const selected = selectedKeys();
        const message = selected.length
            ? `Delete ${selected.length} selected item(s) from your cart?`
            : 'Remove all items from your cart?';
        if (!window.confirm(message)) event.preventDefault();
    });

    checkboxes().forEach((box) => { box.checked = true; });
    syncSelection();
});

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
            document.getElementById('cartBulkDeleteForm')?.remove();
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
            const removedKey = form.dataset.cartKey;
            if (removedKey) {
                document.querySelectorAll('[data-cart-line]').forEach((row) => {
                    if (row.dataset.cartLine === removedKey) row.remove();
                });
            }
            const summary = document.querySelector('.cart-summary-card');
            if (summary && result.summary_html) summary.outerHTML = result.summary_html;
            window.syncCartSelection?.();
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
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h5 class="font-serif fw-bold fs-5 mb-0"><i class="fa-solid fa-bag-shopping text-gold me-2"></i> YOUR SHOPPING CART</h5>
        <div class="cart-header-actions {{ !empty($cart) && count($cart) > 0 ? 'cart-has-items' : '' }} d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('shop') }}" class="btn btn-qw-outline-gold btn-sm rounded-pill px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Back to Shop
            </a>
            @if(!empty($cart) && count($cart) > 0)
                <form action="{{ route('cart.clear') }}" method="POST" id="cartBulkDeleteForm">
                    @csrf
                    <div id="cartBulkDeleteKeys"></div>
                    <button type="submit" id="cartBulkDeleteButton" class="btn btn-outline-danger btn-sm rounded-pill px-3" disabled>
                        Remove all items
                    </button>
                </form>
            @endif
        </div>
    </div>

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
                <div class="cart-selection-bar rounded-3 px-3 py-2 mb-3 d-flex align-items-center gap-2">
                    <input type="checkbox" id="cartSelectAll" class="cart-item-select cart-select-all" data-cart-select-all aria-label="Select all products">
                    <label for="cartSelectAll" id="cartSelectionNotice" class="small fw-bold mb-0">All products selected</label>
                </div>
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
                                    <tr data-cart-line="{{ $key }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="cart-item-image-wrap">
                                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 44px; height: 56px; object-fit: cover;">
                                                    <input type="checkbox" class="cart-item-select" data-cart-select name="cart_keys[]" form="cartCheckoutForm" value="{{ $key }}" aria-label="Select {{ $item['name'] }}">
                                                </div>
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
