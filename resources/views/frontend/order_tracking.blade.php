@extends('layouts.app')

@section('title', 'Track Order - ' . $siteName)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h2 class="font-serif fw-bold display-6 mb-2"><i class="fa-solid fa-truck-fast text-gold me-2"></i> TRACK YOUR ORDER</h2>
                <p class="text-muted">Enter your Order Number (e.g. QW-20260820-XXXXX) and mobile number below.</p>
            </div>

            <!-- Lookup Form -->
            <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                <form action="{{ route('order.tracking') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Order Number</label>
                            <input type="text" name="order_number" class="form-control rounded-3" placeholder="QW-20260820-XXXXX" value="{{ request()->order_number }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Mobile Phone Number</label>
                            <input type="tel" name="phone" class="form-control rounded-3" placeholder="10-digit mobile" value="{{ request()->phone }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-qw-gold w-100 rounded-3">TRACK</button>
                        </div>
                    </div>
                </form>
            </div>

            @if(request()->filled('order_number') && !$order)
                <div class="alert alert-danger text-center rounded-3 shadow-sm py-4">
                    <i class="fa-solid fa-circle-xmark fs-2 mb-2"></i>
                    <h5>No Order Found</h5>
                    <p class="mb-0 small">Please check your Order Number and Mobile Number and try again.</p>
                </div>
            @endif

            @if($order)
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom pb-3 mb-4">
                        <div>
                            <span class="text-muted small text-uppercase font-bold">Order Details</span>
                            <h4 class="font-serif fw-bold text-gold mb-0">{{ $order->order_number }}</h4>
                        </div>
                        <div class="mt-2 mt-sm-0 text-sm-end">
                            @if($order->order_status === 'cancelled')
                                <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase">Status: CANCELLED</span>
                            @elseif(!$order->is_dispatched_to_courier)
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-box-open me-1"></i> Packing Process
                                </span>
                            @else
                                <span class="badge bg-success fs-6 px-3 py-2 text-uppercase">
                                    <i class="fa-solid fa-truck-fast me-1"></i> Handed Over to Courier Partner
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Courier Handover & Live Tracking Display -->
                    @if($order->is_dispatched_to_courier)
                        <div class="p-4 bg-light rounded-4 border border-success mb-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <h6 class="fw-bold mb-0 text-success"><i class="fa-solid fa-circle-check me-2"></i> Handed Over to Courier Office</h6>
                                <span class="small text-muted">Dispatched Date: {{ \Carbon\Carbon::parse($order->dispatched_at)->format('M d, Y') }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-6">
                                    <div class="small text-muted text-uppercase fw-bold">Courier Service Partner</div>
                                    <div class="fw-bold text-dark fs-6">{{ $order->courier_partner ?: 'Express Courier' }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="small text-muted text-uppercase fw-bold">Live Tracking Code / AWB</div>
                                    <div class="font-monospace fw-bold text-dark fs-6">{{ $order->tracking_number ?: 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-light rounded-3 border mb-4 text-center">
                            <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-boxes-packing text-warning me-2"></i> Status: Packing Process</div>
                            <p class="mb-0 small text-muted">Your order is currently being quality checked, packed, and prepared for courier handover.</p>
                        </div>
                    @endif

                    <!-- Visual Timeline -->
                    @php
                        $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered'];
                        $currentIdx = array_search($order->order_status, $statuses);
                        if ($currentIdx === false && $order->order_status === 'cancelled') {
                            $currentIdx = -1;
                        }
                    @endphp

                    @if($order->order_status === 'cancelled')
                        <div class="alert alert-danger text-center fw-bold py-3 mb-4 rounded-3">
                            <i class="fa-solid fa-ban me-2"></i> THIS ORDER HAS BEEN CANCELLED
                        </div>
                    @else
                        <div class="position-relative my-4 py-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $currentIdx >= 0 ? (($currentIdx + 1) / count($statuses)) * 100 : 0 }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between text-center position-relative mt-n3" style="margin-top: -24px;">
                                @foreach($statuses as $idx => $st)
                                    <div>
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $idx <= $currentIdx ? 'bg-gold text-white' : 'bg-light border text-muted' }}" style="width: 38px; height: 38px; font-size: 14px;">
                                            <i class="fa-solid fa-{{ $idx <= $currentIdx ? 'check' : 'circle' }}"></i>
                                        </div>
                                        <div class="small fw-semibold mt-1 text-uppercase text-nowrap d-none d-sm-block" style="font-size: 0.7rem;">{{ $st }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="row g-4 mt-4">
                        <div class="col-sm-6">
                            <div class="bg-light p-3 rounded-3">
                                <h6 class="font-serif fw-bold mb-2">Delivery Address</h6>
                                <p class="mb-1 fw-bold small">{{ $order->customer_name }}</p>
                                <p class="mb-0 text-muted small">
                                    {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pin_code }}
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="bg-light p-3 rounded-3">
                                <h6 class="font-serif fw-bold mb-2">Payment Summary</h6>
                                <p class="mb-1 small">Method: <strong class="text-uppercase">{{ $order->payment_method }}</strong></p>
                                <p class="mb-1 small">Status: <strong class="text-uppercase text-success">{{ $order->payment_status }}</strong></p>
                                <p class="mb-0 small">Grand Total: <strong class="text-gold fs-6">₹{{ number_format($order->grand_total, 2) }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
