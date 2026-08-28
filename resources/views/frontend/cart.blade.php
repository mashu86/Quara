@extends('layouts.app')

@section('title', 'Shopping Cart - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

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
            <div class="alert alert-warning rounded-3 shadow-sm mb-3 py-2 px-3 small">
                <h6 class="fw-bold mb-1 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> Stock Availability Notice</h6>
                <ul class="mb-0 ps-3 small">
                    @foreach($stockValidation['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 50px; height: 62px; object-fit: cover;">
                                                <div>
                                                    <h6 class="font-serif fw-bold mb-0 small">
                                                        <a href="{{ route('product.detail', $item['slug']) }}" class="text-dark text-decoration-none">{{ $item['name'] }}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-dark px-2 py-1" style="font-size: 0.7rem;">{{ $item['size'] }}</span></td>
                                        <td>
                                            <span class="fw-bold">₹{{ number_format($item['final_price'], 2) }}</span>
                                            @if($item['discount_amount'] > 0)
                                                <div class="text-muted text-decoration-line-through style-small" style="font-size: 0.7rem;">₹{{ number_format($item['price'], 2) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('cart.update', $key) }}" method="POST" class="d-flex align-items-center" style="max-width: 100px;">
                                                @csrf
                                                <input type="number" name="quantity" class="form-control form-control-sm text-center font-bold me-1 py-1" value="{{ $item['quantity'] }}" min="1" onchange="this.form.submit()" style="font-size: 0.8rem;">
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold text-gold fs-6">₹{{ number_format($item['subtotal'], 2) }}</td>
                                        <td class="text-end">
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
                <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border sticky-top" style="top: 90px;">
                    <h6 class="font-serif fw-bold mb-3 pb-2 border-bottom">ORDER SUMMARY</h6>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Bag Subtotal</span>
                        <span class="fw-semibold">₹{{ number_format($summary['subtotal'], 2) }}</span>
                    </div>

                    @if($summary['discount'] > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Total Discount</span>
                            <span>-₹{{ number_format($summary['discount'], 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">Shipping Charge</span>
                        @if($summary['shipping'] > 0)
                            <span class="fw-bold text-dark">₹{{ number_format($summary['shipping'], 2) }}</span>
                        @else
                            <span class="text-success fw-semibold">FREE</span>
                        @endif
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between mb-3 fs-6 fw-bold">
                        <span>Grand Total</span>
                        <span class="text-gold">₹{{ number_format($summary['grand_total'], 2) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn btn-qw-gold btn-sm w-100 rounded-pill shadow-sm py-1-5 fw-bold" style="font-size: 0.78rem; padding-top: 6px; padding-bottom: 6px;">
                        PROCEED TO CHECKOUT <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
