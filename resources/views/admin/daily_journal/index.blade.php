@extends('layouts.admin')

@section('title', 'Daily Journal & Operations - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header & Date Picker Navigation -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-5">
                    <h3 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-primary"></i> Daily Business Journal
                    </h3>
                    <p class="text-muted small mb-0">
                        Comprehensive daily log of sales, orders, products, expenses, and returns.
                    </p>
                </div>

                <div class="col-12 col-lg-7">
                    <form action="{{ route('admin.daily-journal.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <a href="{{ route('admin.daily-journal.index', ['date' => $prevDateStr]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Previous Day">
                            <i class="fa-solid fa-chevron-left me-1"></i> Prev
                        </a>

                        <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="max-width: 200px;">
                            <span class="input-group-text bg-light border-0"><i class="fa-solid fa-calendar text-primary"></i></span>
                            <input type="date" name="date" class="form-control border-0 fw-bold text-dark" value="{{ $selectedDateStr }}" onchange="this.form.submit()">
                        </div>

                        <a href="{{ route('admin.daily-journal.index', ['date' => $nextDateStr]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Next Day">
                            Next <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>

                        @if(!$isToday)
                            <a href="{{ route('admin.daily-journal.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Today
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Date Banner -->
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <h5 class="fw-bold text-dark mb-0 font-monospace">
            <i class="fa-regular fa-calendar-days text-secondary me-2"></i> Journal for: 
            <span class="text-primary border-bottom border-2 border-primary pb-1">
                {{ \Carbon\Carbon::parse($selectedDateStr)->format('d F Y (l)') }}
            </span>
        </h5>
        @if($isToday)
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-1 border border-success rounded-pill">
                <i class="fa-solid fa-circle me-1 animate-pulse" style="font-size: 0.6rem;"></i> Live Today
            </span>
        @endif
    </div>

    <!-- 5 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Sales -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card border-start border-4 border-success bg-white shadow-sm p-3 rounded-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Total Sales</div>
                        <h3 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($grossSales, 2) }}</h3>
                        <div class="text-muted extra-small mt-1">Net: ₹{{ number_format($netSales, 2) }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success rounded-circle p-3">
                        <i class="fa-solid fa-indian-rupee-sign fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders Received -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card border-start border-4 border-primary bg-white shadow-sm p-3 rounded-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Orders Received</div>
                        <h3 class="fw-bold mb-0 text-primary mt-1">{{ $totalOrdersCount }}</h3>
                        <div class="text-muted extra-small mt-1">{{ $paidOrdersCount }} Paid | {{ $pendingOrdersCount }} Pending</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary rounded-circle p-3">
                        <i class="fa-solid fa-cart-shopping fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sold Products (Pcs) -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card border-start border-4 border-info bg-white shadow-sm p-3 rounded-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Sold Items (Pcs)</div>
                        <h3 class="fw-bold mb-0 text-info mt-1">{{ $totalSoldPcs }} <span class="fs-6 fw-normal text-muted">pcs</span></h3>
                        <div class="text-muted extra-small mt-1">across paid orders</div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info rounded-circle p-3">
                        <i class="fa-solid fa-shirt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card border-start border-4 border-danger bg-white shadow-sm p-3 rounded-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Total Expenses</div>
                        <h3 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($totalExpenses, 2) }}</h3>
                        <div class="text-muted extra-small mt-1">{{ count($expenses) }} expense items</div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle p-3">
                        <i class="fa-solid fa-arrow-trend-down fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Net Profit/Loss -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card border-start border-4 {{ $isProfit ? 'border-success' : 'border-danger' }} bg-white shadow-sm p-3 rounded-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Daily Profit/Loss</div>
                        <h3 class="fw-bold mb-0 {{ $isProfit ? 'text-success' : 'text-danger' }} mt-1">
                            ₹{{ number_format(abs($netProfitLoss), 2) }}
                        </h3>
                        <div class="extra-small font-bold mt-1 {{ $isProfit ? 'text-success' : 'text-danger' }}">
                            {{ $isProfit ? '▲ Net Profit' : '▼ Net Loss' }}
                        </div>
                    </div>
                    <div class="stat-icon {{ $isProfit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-circle p-3">
                        <i class="fa-solid {{ $isProfit ? 'fa-chart-line' : 'fa-chart-line-down' }} fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-dark text-white p-3">
            <ul class="nav nav-tabs card-header-tabs border-0" id="journalTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-white px-4" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab">
                        <i class="fa-solid fa-bag-shopping me-2 text-warning"></i> Orders & Sold Items ({{ count($orders) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-4" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-pane" type="button" role="tab">
                        <i class="fa-solid fa-receipt me-2 text-danger"></i> Expenses ({{ count($expenses) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-4" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns-pane" type="button" role="tab">
                        <i class="fa-solid fa-rotate-left me-2 text-info"></i> Refunds & Operations ({{ count($refunds) + count($operations) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-4" id="booked-tab" data-bs-toggle="tab" data-bs-target="#booked-pane" type="button" role="tab">
                        <i class="fa-solid fa-bookmark me-2 text-warning"></i> Booked / Reserved Products ({{ count($bookedProductSizes) }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="journalTabContent">
                <!-- TAB 1: Orders & Sold Products -->
                <div class="tab-pane fade show active" id="orders-pane" role="tabpanel" tabindex="0">
                    @if(count($orders) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-muted mb-2"><i class="fa-solid fa-box-open"></i></div>
                            <h5 class="fw-bold text-muted">No orders recorded on this date.</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Order # & Time</th>
                                        <th>Customer Info</th>
                                        <th>Products Purchased (Click image to zoom)</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Order Status</th>
                                        <th class="pe-3 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $ord)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="fw-bold text-primary text-decoration-none">
                                                    #{{ $ord->order_number }}
                                                </a>
                                                <div class="text-muted extra-small" style="font-size: 0.72rem;">
                                                    <i class="fa-regular fa-clock me-1"></i> {{ $ord->created_at->format('h:i A') }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark small">{{ $ord->customer_name }}</div>
                                                <div class="text-muted small" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-phone me-1"></i> {{ $ord->customer_phone }}
                                                </div>
                                            </td>
                                            <td style="min-width: 280px;">
                                                <div class="d-flex flex-column gap-2 py-1">
                                                    @foreach($ord->items as $item)
                                                        @php
                                                            $prod = $item->product;
                                                            $imgUrl = null;
                                                            if ($prod && $prod->images && count($prod->images) > 0) {
                                                                $imgUrl = asset('storage/' . $prod->images[0]->image_path);
                                                            }
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2 bg-light p-1 px-2 rounded-3 border">
                                                            @if($imgUrl)
                                                                <img src="{{ $imgUrl }}" 
                                                                     alt="{{ $item->product_name }}" 
                                                                     class="rounded shadow-sm cursor-pointer product-img-thumbnail" 
                                                                     style="width: 44px; height: 44px; object-fit: cover; border: 1px solid #ddd;"
                                                                     onclick="openImageModal('{{ $imgUrl }}', '{{ addslashes($item->product_name) }}', '{{ $item->size }}', '₹{{ number_format($item->price, 2) }}')"
                                                                     title="Click to view full image">
                                                            @else
                                                                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 0.7rem;">
                                                                    No Image
                                                                </div>
                                                            @endif

                                                            <div class="lh-1">
                                                                <div class="fw-bold text-dark extra-small" style="font-size: 0.78rem;">
                                                                    {{ Str::limit($item->product_name, 30) }}
                                                                </div>
                                                                <div class="text-muted extra-small mt-1" style="font-size: 0.72rem;">
                                                                    Size: <span class="badge bg-secondary-subtle text-dark p-1">{{ $item->size ?: 'N/A' }}</span> 
                                                                    | Qty: <span class="fw-bold text-dark">{{ $item->quantity }}</span> 
                                                                    | ₹{{ number_format($item->price, 2) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6">₹{{ number_format($ord->grand_total, 2) }}</div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ord->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1">
                                                    {{ strtoupper($ord->payment_status) }}
                                                </span>
                                                <div class="extra-small text-muted mt-1" style="font-size: 0.70rem;">
                                                    {{ strtoupper($ord->payment_method) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                                    {{ ucfirst($ord->order_status) }}
                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: Expenses -->
                <div class="tab-pane fade" id="expenses-pane" role="tabpanel" tabindex="0">
                    @if(count($expenses) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-muted mb-2"><i class="fa-solid fa-receipt"></i></div>
                            <h5 class="fw-bold text-muted">No expenses recorded on this date.</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Expense Name</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Logged By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $exp)
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">{{ $exp->title }}</td>
                                            <td><span class="badge bg-secondary">{{ $exp->category ?? 'General' }}</span></td>
                                            <td class="fw-bold text-danger fs-6">₹{{ number_format($exp->amount, 2) }}</td>
                                            <td class="text-muted small">{{ $exp->description ?: '-' }}</td>
                                            <td class="small text-secondary">{{ $exp->spent_by ?: 'Admin' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: Returns & Operations -->
                <div class="tab-pane fade" id="returns-pane" role="tabpanel" tabindex="0">
                    @if(count($refunds) === 0 && count($operations) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-muted mb-2"><i class="fa-solid fa-rotate-left"></i></div>
                            <h5 class="fw-bold text-muted">No refunds or return operations recorded on this date.</h5>
                        </div>
                    @else
                        <div class="p-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i> Order Refunds</h6>
                            @if(count($refunds) > 0)
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Refund Amount</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($refunds as $ref)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.orders.show', $ref->order_id) }}" class="fw-bold text-primary">
                                                            #{{ $ref->order ? $ref->order->order_number : $ref->order_id }}
                                                        </a>
                                                    </td>
                                                    <td class="fw-bold text-danger">₹{{ number_format($ref->refund_amount, 2) }}</td>
                                                    <td>{{ $ref->refund_reason ?: 'Customer Return / Cancellation' }}</td>
                                                    <td class="small text-muted">{{ $ref->created_at->format('d M Y, h:i A') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small">No refunds issued on this date.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- TAB 4: Booked / Reserved Products -->
                <div class="tab-pane fade" id="booked-pane" role="tabpanel" tabindex="0">
                    @if(count($bookedProductSizes) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-success mb-2"><i class="fa-solid fa-circle-check"></i></div>
                            <h5 class="fw-bold text-dark">No products are currently booked or reserved!</h5>
                            <p class="text-muted small">All stock items are 100% available on the public website.</p>
                        </div>
                    @else
                        <div class="p-3">
                            <div class="alert alert-warning border-warning shadow-sm rounded-3 mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                                    <strong>Booked / Reserved Inventory Notice:</strong> 
                                    Stock items listed here are locked in the Internal Reserved Pool. Click <strong>[ Release / Unbook Stock ]</strong> to restore public availability on the shop.
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Product Image & Name</th>
                                            <th>Size</th>
                                            <th>Reserved Quantity</th>
                                            <th>Total Stock</th>
                                            <th>Available Stock (Public)</th>
                                            <th class="pe-3 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookedProductSizes as $bps)
                                            @php
                                                $prod = $bps->product;
                                                $imgUrl = null;
                                                if ($prod && $prod->images && count($prod->images) > 0) {
                                                    $imgUrl = asset('storage/' . $prod->images[0]->image_path);
                                                }
                                            @endphp
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($imgUrl)
                                                            <img src="{{ $imgUrl }}" 
                                                                 alt="{{ $prod->name }}" 
                                                                 class="rounded shadow-sm cursor-pointer product-img-thumbnail" 
                                                                 style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd;"
                                                                 onclick="openImageModal('{{ $imgUrl }}', '{{ addslashes($prod->name) }}', '{{ $bps->size }}', '₹{{ number_format($prod->selling_price, 2) }}')">
                                                        @else
                                                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 0.7rem;">
                                                                No Image
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $prod ? $prod->name : 'Product #' . $bps->product_id }}</div>
                                                            <div class="text-muted extra-small">SKU: {{ $prod ? $prod->sku : 'N/A' }} | Price: ₹{{ number_format($prod ? $prod->selling_price : 0, 2) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-dark fs-6">{{ $bps->size ?: 'Free Size' }}</span></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2 border border-warning rounded-pill">
                                                        <i class="fa-solid fa-lock me-1"></i> {{ $bps->reserved_stock }} Reserved
                                                    </span>
                                                </td>
                                                <td><span class="fw-bold text-dark">{{ $bps->stock }}</span></td>
                                                <td>
                                                    <span class="badge {{ $bps->available_stock > 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                                        {{ $bps->available_stock }} Pcs Available
                                                    </span>
                                                </td>
                                                <td class="pe-3 text-end">
                                                    <form action="{{ route('admin.daily-journal.release-reserved-stock') }}" method="POST" onsubmit="return confirm('Are you sure you want to release this reserved stock back into public inventory?');">
                                                        @csrf
                                                        <input type="hidden" name="product_size_id" value="{{ $bps->id }}">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                            <i class="fa-solid fa-lock-open me-1"></i> Release / Unbook Stock
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive High-Resolution Product Image Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3">
                <h5 class="modal-title fw-bold font-serif text-warning" id="modalProductName">Product Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-light position-relative">
                <img id="modalPreviewImage" src="" alt="Product Image" class="img-fluid" style="max-height: 75vh; object-fit: contain; width: 100%;">
                <div class="p-3 bg-white border-top text-start d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary fs-6 me-2" id="modalProductSize">Size: Free Size</span>
                        <span class="fw-bold text-success fs-5" id="modalProductPrice">₹0.00</span>
                    </div>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openImageModal(imgUrl, productName, size, price) {
        document.getElementById('modalPreviewImage').src = imgUrl;
        document.getElementById('modalProductName').innerText = productName;
        document.getElementById('modalProductSize').innerText = 'Size: ' + (size || 'N/A');
        document.getElementById('modalProductPrice').innerText = price;
        
        var modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    }
</script>
@endpush

<style>
    .product-img-thumbnail {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .product-img-thumbnail:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    .animate-pulse {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }
</style>
@endsection
