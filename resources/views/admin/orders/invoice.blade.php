<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - QUARA WALDROP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .invoice-card {
            background: #fff;
            max-width: 850px;
            margin: 30px auto;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .invoice-header {
            background: linear-gradient(135deg, #111111 0%, #2b2b2b 100%);
            color: #fff;
            padding: 35px 40px;
        }
        .brand-logo-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: #ffc107;
        }
        .invoice-badge {
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 50px;
        }
        .table-invoice th {
            background-color: #f1f3f5;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-card {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center pt-4">
    <button onclick="window.print()" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm me-2">
        <i class="fa-solid fa-print me-1"></i> Print Invoice
    </button>
    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-dark rounded-pill px-4 fw-bold shadow-sm">
        &larr; Back to Order Details
    </a>
</div>

<div class="invoice-card">
    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="brand-logo-text mb-1">QUARA WALDROP</div>
            <p class="mb-0 small text-light opacity-75">Modest & Affordable Ladies Fashion</p>
            <p class="mb-0 small text-light opacity-75">Naduvil, Kannur, Kerala - 670582 | Ph: +91 98765 43210</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold mb-1 text-warning">INVOICE</h2>
            <div class="small text-light opacity-75">Invoice #: INV-{{ $order->order_number }}</div>
            <div class="small text-light opacity-75">Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="p-4 p-sm-5">
        <div class="row g-4 mb-4 pb-3 border-bottom">
            <div class="col-sm-6">
                <h6 class="text-muted text-uppercase fw-bold small mb-2">Billed & Shipped To:</h6>
                <h5 class="fw-bold mb-1 text-dark">{{ $order->customer_name }}</h5>
                <p class="mb-1 text-secondary small">
                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                    {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }}<br>
                    {{ $order->city }}, {{ $order->district }}, {{ $order->state }} - <strong>{{ $order->pin_code }}</strong>
                </p>
                <p class="mb-1 text-secondary small"><i class="fa-solid fa-phone me-1"></i> {{ $order->customer_phone }}</p>
                @if($order->customer_email)
                    <p class="mb-0 text-secondary small"><i class="fa-solid fa-envelope me-1"></i> {{ $order->customer_email }}</p>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="text-muted text-uppercase fw-bold small mb-2">Order Information:</h6>
                <p class="mb-1"><strong>Order No:</strong> #{{ $order->order_number }}</p>
                <p class="mb-1">
                    <strong>Payment Method:</strong> 
                    <span class="text-uppercase fw-bold">{{ $order->payment_method }}</span>
                </p>
                <p class="mb-1">
                    <strong>Payment Status:</strong> 
                    @if($order->payment_status === 'paid')
                        <span class="badge bg-success invoice-badge">PAID</span>
                    @else
                        <span class="badge bg-warning text-dark invoice-badge">{{ strtoupper($order->payment_status) }}</span>
                    @endif
                </p>
                <p class="mb-0">
                    <strong>Order Status:</strong> 
                    <span class="badge bg-dark invoice-badge">{{ strtoupper($order->order_status) }}</span>
                </p>
                @if($order->is_dispatched_to_courier)
                    <p class="mb-0 mt-1 text-success small fw-bold">
                        <i class="fa-solid fa-truck-fast me-1"></i> Handed Over to {{ $order->courier_partner ?? 'Courier' }}
                        @if($order->tracking_number)
                            (AWB: {{ $order->tracking_number }})
                        @endif
                    </p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <h6 class="fw-bold mb-3">Order Items Breakdown</h6>
        <div class="table-responsive mb-4">
            <table class="table align-middle table-invoice">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product Details</th>
                        <th class="text-center">Size</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $item->size }}</span>
                            </td>
                            <td class="text-center">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Summary -->
        <div class="row justify-content-end">
            <div class="col-md-6 col-lg-5">
                <div class="bg-light p-3 rounded-3">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Discount / Savings:</span>
                            <span class="fw-bold">- ₹{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Shipping & Handing:</span>
                        <span class="fw-bold">
                            {{ $order->shipping > 0 ? '₹' . number_format($order->shipping, 2) : 'FREE' }}
                        </span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5 fw-bold text-dark">
                        <span>Grand Total:</span>
                        <span class="text-dark">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 pt-4 border-top">
            <p class="mb-1 fw-bold text-dark">Thank you for shopping with QUARA WALDROP!</p>
            <p class="mb-0 text-muted small">For any support or query regarding this order, please email quarawaldrop@gmail.com or WhatsApp us.</p>
        </div>
    </div>
</div>

</body>
</html>
