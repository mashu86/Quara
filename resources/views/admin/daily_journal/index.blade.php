@extends('layouts.admin')

@section('title', 'Daily Journal & Operations - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-2 py-md-3">
    <!-- Header & Date Picker Navigation -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-2 g-md-3">
                <div class="col-12 col-lg-5 text-center text-lg-start">
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center justify-content-center justify-content-lg-start gap-2 fs-5 fs-md-4">
                        <i class="fa-solid fa-calendar-check text-primary"></i> Daily Business Journal
                    </h4>
                    <p class="text-muted extra-small mb-0 d-none d-sm-block">
                        Comprehensive daily log of sales, orders, products, expenses, and returns.
                    </p>
                </div>

                <div class="col-12 col-lg-7">
                    <form action="{{ route('admin.daily-journal.index') }}" method="GET" class="d-flex align-items-center justify-content-center justify-content-lg-end gap-1.5 gap-md-2 flex-nowrap overflow-x-auto">
                        <a href="{{ route('admin.daily-journal.index', ['date' => $prevDateStr]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 px-md-3 text-nowrap" title="Previous Day">
                            <i class="fa-solid fa-chevron-left me-1"></i> Prev
                        </a>

                        <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="max-width: 160px; min-width: 130px;">
                            <span class="input-group-text bg-light border-0 px-2"><i class="fa-solid fa-calendar text-primary"></i></span>
                            <input type="date" name="date" class="form-control border-0 fw-bold text-dark px-1 extra-small" value="{{ $selectedDateStr }}" onchange="this.form.submit()">
                        </div>

                        <a href="{{ route('admin.daily-journal.index', ['date' => $nextDateStr]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 px-md-3 text-nowrap" title="Next Day">
                            Next <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>

                        @if(!$isToday)
                            <a href="{{ route('admin.daily-journal.index') }}" class="btn btn-primary btn-sm rounded-pill px-2.5 px-md-3 fw-bold text-nowrap">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Today
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Date Banner -->
    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3 px-1">
        <h6 class="fw-bold text-dark mb-0 font-monospace fs-6">
            <i class="fa-regular fa-calendar-days text-secondary me-1"></i> 
            <span class="text-primary border-bottom border-2 border-primary pb-0.5">
                {{ \Carbon\Carbon::parse($selectedDateStr)->format('d F Y (l)') }}
            </span>
        </h6>
        @if($isToday)
            <span class="badge bg-success-subtle text-success extra-small px-2.5 py-1 border border-success rounded-pill">
                <i class="fa-solid fa-circle me-1 animate-pulse" style="font-size: 0.55rem;"></i> Live Today
            </span>
        @endif
    </div>

    <!-- 5 Compact Responsive KPI Summary Cards -->
    <div class="row g-2 mb-3">
        <!-- Total Sales -->
        <div class="col-6 col-md-4 col-xl-2-4">
            <div class="stat-card border-start border-3 border-success bg-white shadow-sm p-2 px-2.5 px-md-3 rounded-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold" style="font-size: 0.65rem;">Successful Sales</div>
                        <h4 class="fw-bold mb-0 text-success mt-1 fs-6 fs-md-5 text-truncate">₹{{ number_format($grossSales, 2) }}</h4>
                        <div class="text-muted extra-small text-truncate" style="font-size: 0.65rem;">Net: ₹{{ number_format($netSales, 2) }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success rounded-circle p-1.5 p-md-2 d-none d-sm-flex">
                        <i class="fa-solid fa-indian-rupee-sign fs-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders Received -->
        <div class="col-6 col-md-4 col-xl-2-4">
            <div class="stat-card border-start border-3 border-primary bg-white shadow-sm p-2 px-2.5 px-md-3 rounded-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold" style="font-size: 0.65rem;">Successful Orders</div>
                        <h4 class="fw-bold mb-0 text-primary mt-1 fs-6 fs-md-5">{{ count($orders) }}</h4>
                        <div class="text-muted extra-small text-truncate" style="font-size: 0.65rem;">Paid / Confirmed</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary rounded-circle p-1.5 p-md-2 d-none d-sm-flex">
                        <i class="fa-solid fa-circle-check fs-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sold Products (Pcs) -->
        <div class="col-6 col-md-4 col-xl-2-4">
            <div class="stat-card border-start border-3 border-info bg-white shadow-sm p-2 px-2.5 px-md-3 rounded-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold" style="font-size: 0.65rem;">Sold Items (Pcs)</div>
                        <h4 class="fw-bold mb-0 text-info mt-1 fs-6 fs-md-5">{{ $totalSoldPcs }} <span class="fs-7 fw-normal text-muted">pcs</span></h4>
                        <div class="text-muted extra-small text-truncate" style="font-size: 0.65rem;">paid orders</div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info rounded-circle p-1.5 p-md-2 d-none d-sm-flex">
                        <i class="fa-solid fa-shirt fs-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-6 col-md-6 col-xl-2-4">
            <div class="stat-card border-start border-3 border-danger bg-white shadow-sm p-2 px-2.5 px-md-3 rounded-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold" style="font-size: 0.65rem;">Total Expenses</div>
                        <h4 class="fw-bold mb-0 text-danger mt-1 fs-6 fs-md-5 text-truncate">₹{{ number_format($totalExpenses, 2) }}</h4>
                        <div class="text-muted extra-small text-truncate" style="font-size: 0.65rem;">{{ count($expenses) }} items</div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle p-1.5 p-md-2 d-none d-sm-flex">
                        <i class="fa-solid fa-arrow-trend-down fs-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Net Profit/Loss -->
        <div class="col-12 col-md-6 col-xl-2-4">
            <div class="stat-card border-start border-3 {{ $isProfit ? 'border-success' : 'border-danger' }} bg-white shadow-sm p-2 px-2.5 px-md-3 rounded-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold" style="font-size: 0.65rem;">Daily Net Profit/Loss</div>
                        <h4 class="fw-bold mb-0 {{ $isProfit ? 'text-success' : 'text-danger' }} mt-1 fs-6 fs-md-5 text-truncate">
                            ₹{{ number_format(abs($netProfitLoss), 2) }}
                        </h4>
                        <div class="extra-small font-bold text-truncate {{ $isProfit ? 'text-success' : 'text-danger' }}" style="font-size: 0.65rem;">
                            {{ $isProfit ? '▲ Net Profit' : '▼ Net Loss' }}
                        </div>
                    </div>
                    <div class="stat-icon {{ $isProfit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-circle p-1.5 p-md-2 d-none d-sm-flex">
                        <i class="fa-solid {{ $isProfit ? 'fa-chart-line' : 'fa-chart-line-down' }} fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs with Horizontal Mobile Scrolling -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-dark text-white p-1.5 px-2 px-md-3">
            <ul class="nav nav-tabs card-header-tabs border-0 nav-tabs-scrollable" id="journalTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-white px-2.5 px-md-3 py-1.5 extra-small text-nowrap" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i> Successful Orders ({{ count($orders) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-2.5 px-md-3 py-1.5 extra-small text-nowrap" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-pane" type="button" role="tab">
                        <i class="fa-solid fa-receipt me-1 text-danger"></i> Expenses ({{ count($expenses) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-2.5 px-md-3 py-1.5 extra-small text-nowrap" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns-pane" type="button" role="tab">
                        <i class="fa-solid fa-rotate-left me-1 text-info"></i> Refunds & Ops ({{ count($refunds) + count($operations) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-white px-2.5 px-md-3 py-1.5 extra-small text-nowrap" id="booked-tab" data-bs-toggle="tab" data-bs-target="#booked-pane" type="button" role="tab">
                        <i class="fa-solid fa-bookmark me-1 text-warning"></i> Booked Products ({{ count($bookedProductSizes) }})
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
                            <h5 class="fw-bold text-muted fs-6">No successful orders recorded on this date.</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-2 ps-md-3">Order # & Time</th>
                                        <th>Customer Info</th>
                                        <th>Purchased Products</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th class="pe-2 pe-md-3 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $ord)
                                        <tr>
                                            <td class="ps-2 ps-md-3">
                                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="fw-bold text-primary text-decoration-none extra-small">
                                                    #{{ $ord->order_number }}
                                                </a>
                                                <div class="text-muted extra-small" style="font-size: 0.70rem;">
                                                    <i class="fa-regular fa-clock me-1"></i> {{ $ord->created_at->format('h:i A') }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark extra-small">{{ $ord->customer_name }}</div>
                                                <a href="tel:{{ $ord->customer_phone }}" class="text-muted text-decoration-none extra-small" style="font-size: 0.72rem;">
                                                    <i class="fa-solid fa-phone me-1"></i> {{ $ord->customer_phone }}
                                                </a>
                                            </td>
                                            <td style="min-width: 220px;">
                                                <div class="d-flex flex-column gap-1.5 py-1">
                                                    @foreach($ord->items as $item)
                                                        @php
                                                            $prod = $item->product;
                                                            $imgUrl = $prod ? $prod->primary_image_url : asset('media/logo.png');
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2 bg-light p-1 px-2 rounded-3 border">
                                                            @if($imgUrl)
                                                                <img src="{{ $imgUrl }}" 
                                                                     alt="{{ $item->product_name }}" 
                                                                     class="rounded shadow-sm cursor-pointer product-img-thumbnail flex-shrink-0" 
                                                                     style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;"
                                                                     onclick="openImageModal('{{ $imgUrl }}', '{{ addslashes($item->product_name) }}', '{{ $item->size }}', '₹{{ number_format($item->price, 2) }}')"
                                                                     title="Click to view full image"
                                                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/80?text=No+Image';">
                                                            @else
                                                                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.65rem;">
                                                                    No Image
                                                                </div>
                                                            @endif

                                                            <div class="lh-1 overflow-hidden">
                                                                <div class="fw-bold text-dark extra-small text-truncate" style="font-size: 0.75rem; max-width: 170px;">
                                                                    {{ $item->product_name }}
                                                                </div>
                                                                <div class="text-muted extra-small mt-1" style="font-size: 0.70rem;">
                                                                    Size: <span class="badge bg-secondary-subtle text-dark p-0.5 px-1">{{ $item->size ?: 'N/A' }}</span> 
                                                                    | Qty: <span class="fw-bold text-dark">{{ $item->quantity }}</span> 
                                                                    | ₹{{ number_format($item->price, 2) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark small">₹{{ number_format($ord->grand_total, 2) }}</div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ord->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} extra-small px-2 py-0.5">
                                                    {{ strtoupper($ord->payment_status) }}
                                                </span>
                                                <div class="extra-small text-muted mt-0.5" style="font-size: 0.68rem;">
                                                    {{ strtoupper($ord->payment_method) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info extra-small px-2 py-0.5">
                                                    {{ ucfirst($ord->order_status) }}
                                                </span>
                                            </td>
                                            <td class="pe-2 pe-md-3 text-end">
                                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-xs btn-sm btn-outline-primary rounded-pill px-2.5 py-1 extra-small">
                                                    View
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
                            <h5 class="fw-bold text-muted fs-6">No expenses recorded on this date.</h5>
                        </div>
                    @else
                        <div class="p-2 p-md-3">
                            <div class="row g-2">
                                @foreach($expenses as $exp)
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-3 border-danger bg-white">
                                            <div class="card-body p-2 px-2.5 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="badge bg-danger-subtle text-danger font-monospace border border-danger-subtle px-1.5 py-0.5 extra-small" style="font-size: 0.65rem;">
                                                            <i class="fa-solid fa-tag me-1"></i> {{ $exp->category ?? 'General' }}
                                                        </span>
                                                        <span class="fw-bold text-danger fs-6">₹{{ number_format($exp->amount, 2) }}</span>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1 extra-small">{{ $exp->title }}</h6>
                                                    @if($exp->description)
                                                        <p class="text-muted extra-small mb-1 text-truncate" style="font-size: 0.72rem;">{{ $exp->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="pt-1.5 border-top d-flex align-items-center justify-content-between extra-small text-muted mt-1" style="font-size: 0.68rem;">
                                                    <span><i class="fa-regular fa-user me-1"></i> {{ $exp->spent_by ?: 'Admin' }}</span>
                                                    <span><i class="fa-regular fa-clock me-1"></i> {{ $exp->created_at ? $exp->created_at->format('h:i A') : '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: Returns & Operations -->
                <div class="tab-pane fade" id="returns-pane" role="tabpanel" tabindex="0">
                    @if(count($refunds) === 0 && count($operations) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-muted mb-2"><i class="fa-solid fa-rotate-left"></i></div>
                            <h5 class="fw-bold text-muted fs-6">No refunds or return operations recorded on this date.</h5>
                        </div>
                    @else
                        <div class="p-2 p-md-3">
                            <h6 class="fw-bold text-dark mb-2.5 extra-small"><i class="fa-solid fa-hand-holding-dollar me-1.5 text-warning"></i> Order Refunds</h6>
                            @if(count($refunds) > 0)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-2">Order #</th>
                                                <th>Refund Amount</th>
                                                <th>Reason</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($refunds as $ref)
                                                <tr>
                                                    <td class="ps-2">
                                                        <a href="{{ route('admin.orders.show', $ref->order_id) }}" class="fw-bold text-primary extra-small">
                                                            #{{ $ref->order ? $ref->order->order_number : $ref->order_id }}
                                                        </a>
                                                    </td>
                                                    <td class="fw-bold text-danger extra-small">₹{{ number_format($ref->refund_amount, 2) }}</td>
                                                    <td class="extra-small">{{ $ref->refund_reason ?: 'Customer Return / Cancellation' }}</td>
                                                    <td class="extra-small text-muted">{{ $ref->created_at->format('d M, h:i A') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted extra-small">No refunds issued on this date.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- TAB 4: Booked / Reserved Products -->
                <div class="tab-pane fade" id="booked-pane" role="tabpanel" tabindex="0">
                    @if(count($bookedProductSizes) === 0)
                        <div class="text-center py-5">
                            <div class="display-6 text-success mb-2"><i class="fa-solid fa-circle-check"></i></div>
                            <h5 class="fw-bold text-dark fs-6">No products are currently booked or reserved!</h5>
                            <p class="text-muted extra-small">All stock items are 100% available on the public website.</p>
                        </div>
                    @else
                        <div class="p-2 p-md-3">
                            <div class="alert alert-warning border-warning shadow-sm rounded-3 mb-2.5 p-2 px-3 d-flex align-items-center justify-content-between extra-small">
                                <div>
                                    <i class="fa-solid fa-triangle-exclamation me-1.5 text-warning fs-6"></i>
                                    <strong>Booked Inventory Notice:</strong> 
                                    Items here are locked in Internal Reserved Pool. Click <strong>[ Unbook ]</strong> to restore public shop stock.
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-2 ps-md-3">Product Image & Name</th>
                                            <th>Size</th>
                                            <th>Reserved</th>
                                            <th>Total</th>
                                            <th>Public Stock</th>
                                            <th class="pe-2 pe-md-3 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookedProductSizes as $bps)
                                            @php
                                                $prod = $bps->product;
                                                $imgUrl = $prod ? $prod->primary_image_url : asset('media/logo.png');
                                            @endphp
                                            <tr>
                                                <td class="ps-2 ps-md-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($imgUrl)
                                                            <img src="{{ $imgUrl }}" 
                                                                 alt="{{ $prod ? $prod->name : 'Product' }}" 
                                                                 class="rounded shadow-sm cursor-pointer product-img-thumbnail flex-shrink-0" 
                                                                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;"
                                                                 onclick="openImageModal('{{ $imgUrl }}', '{{ addslashes($prod ? $prod->name : 'Product') }}', '{{ $bps->size }}', '₹{{ number_format($prod ? $prod->selling_price : 0, 2) }}')"
                                                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/80?text=No+Image';">
                                                        @else
                                                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.65rem;">
                                                                No Image
                                                            </div>
                                                        @endif
                                                        <div class="overflow-hidden">
                                                            <div class="fw-bold text-dark extra-small text-truncate" style="max-width: 160px;">{{ $prod ? $prod->name : 'Product #' . $bps->product_id }}</div>
                                                            <div class="text-muted extra-small" style="font-size: 0.68rem;">SKU: {{ $prod ? $prod->sku : 'N/A' }} | ₹{{ number_format($prod ? $prod->selling_price : 0, 2) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-dark extra-small">{{ $bps->size ?: 'Free Size' }}</span></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark extra-small px-2 py-1 border border-warning rounded-pill">
                                                        <i class="fa-solid fa-lock me-1"></i> {{ $bps->reserved_stock }}
                                                    </span>
                                                </td>
                                                <td><span class="fw-bold text-dark extra-small">{{ $bps->stock }}</span></td>
                                                <td>
                                                    <span class="badge {{ $bps->available_stock > 0 ? 'bg-success' : 'bg-danger' }} extra-small">
                                                        {{ $bps->available_stock }} Pcs
                                                    </span>
                                                </td>
                                                <td class="pe-2 pe-md-3 text-end">
                                                    <form action="{{ route('admin.daily-journal.release-reserved-stock') }}" method="POST" onsubmit="return confirm('Are you sure you want to release this reserved stock back into public inventory?');">
                                                        @csrf
                                                        <input type="hidden" name="product_size_id" value="{{ $bps->id }}">
                                                        <button type="submit" class="btn btn-xs btn-sm btn-success rounded-pill px-2.5 py-1 extra-small fw-bold">
                                                            <i class="fa-solid fa-lock-open me-1"></i> Unbook
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
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-2.5 px-3">
                <h6 class="modal-title fw-bold text-warning text-truncate" id="modalProductName">Product Preview</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-light position-relative">
                <img id="modalPreviewImage" src="" alt="Product Image" class="img-fluid" style="max-height: 65vh; object-fit: contain; width: 100%;">
                <div class="p-2.5 px-3 bg-white border-top text-start d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary extra-small me-2" id="modalProductSize">Size: Free Size</span>
                        <span class="fw-bold text-success small" id="modalProductPrice">₹0.00</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
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
    .nav-tabs-scrollable {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .nav-tabs-scrollable::-webkit-scrollbar {
        display: none;
    }
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
    @media (max-width: 575.98px) {
        .stat-card {
            padding: 0.5rem !important;
        }
        .nav-tabs-scrollable .nav-link {
            font-size: 0.72rem !important;
            padding: 0.35rem 0.6rem !important;
        }
    }
</style>
@endsection
