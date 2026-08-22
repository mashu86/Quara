@extends('layouts.app')

@section('title', 'Shopping Cart - QUARA WALDROP')

@section('content')
<div class="container py-4">
    <h2 class="font-serif fw-bold display-6 mb-4"><i class="fa-solid fa-bag-shopping text-gold me-2"></i> YOUR SHOPPING CART</h2>

    @if(empty($cart) || count($cart) === 0)
        <div class="bg-white p-5 rounded-4 shadow-sm border text-center my-4">
            <i class="fa-solid fa-bag-shopping text-muted display-1 mb-3"></i>
            <h4 class="font-serif fw-bold">Your cart is currently empty</h4>
            <p class="text-muted mb-4">Looks like you haven't added any trendy pieces to your cart yet.</p>
            <a href="{{ route('shop') }}" class="btn btn-qw-gold btn-lg px-4 rounded-pill">CONTINUE SHOPPING</a>
        </div>
    @else
        @if(!$stockValidation['valid'])
            <div class="alert alert-warning rounded-3 shadow-sm mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Stock Availability Notice</h6>
                <ul class="mb-0 ps-3">
                    @foreach($stockValidation['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $key => $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 60px; height: 75px; object-fit: cover;">
                                                <div>
                                                    <h6 class="font-serif fw-bold mb-0">
                                                        <a href="{{ route('product.detail', $item['slug']) }}" class="text-dark text-decoration-none">{{ $item['name'] }}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-dark px-3 py-2">{{ $item['size'] }}</span></td>
                                        <td>
                                            <span class="fw-bold">₹{{ number_format($item['final_price'], 2) }}</span>
                                            @if($item['discount_amount'] > 0)
                                                <div class="text-muted text-decoration-line-through small">₹{{ number_format($item['price'], 2) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('cart.update', $key) }}" method="POST" class="d-flex align-items-center" style="max-width: 120px;">
                                                @csrf
                                                <input type="number" name="quantity" class="form-control form-control-sm text-center font-bold me-1" value="{{ $item['quantity'] }}" min="1" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold text-gold fs-5">₹{{ number_format($item['subtotal'], 2) }}</td>
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

                <a href="{{ route('shop') }}" class="btn btn-qw-outline rounded-pill"><i class="fa-solid fa-arrow-left me-2"></i> Continue Shopping</a>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="bg-white p-4 rounded-4 shadow-sm border sticky-top" style="top: 90px;">
                    <h5 class="font-serif fw-bold mb-3 pb-2 border-bottom">ORDER SUMMARY</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bag Subtotal</span>
                        <span class="fw-semibold">₹{{ number_format($summary['subtotal'], 2) }}</span>
                    </div>

                    @if($summary['discount'] > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Total Discount</span>
                            <span>-₹{{ number_format($summary['discount'], 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping Charge</span>
                        @if($summary['shipping'] > 0)
                            <span class="fw-bold text-dark">₹{{ number_format($summary['shipping'], 2) }}</span>
                        @else
                            <span class="text-success fw-semibold">FREE</span>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-4 fs-5 fw-bold">
                        <span>Grand Total</span>
                        <span class="text-gold">₹{{ number_format($summary['grand_total'], 2) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn btn-qw-gold btn-lg w-100 rounded-pill shadow-sm">
                        PROCEED TO CHECKOUT <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
