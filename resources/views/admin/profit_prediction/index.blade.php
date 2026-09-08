@extends('layouts.admin')

@section('title', 'Profit Prediction & Business Forecast - ' . $siteName . ' Admin')

@section('content')
<style>
    .kpi-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08) !important;
    }
    .timeline-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
    }
    @media (max-width: 575.98px) {
        .kpi-title { font-size: 0.72rem !important; }
        .kpi-val { font-size: 1.15rem !important; }
        .kpi-sub { font-size: 0.65rem !important; }
    }
</style>

<div class="mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark" style="font-size: 1.15rem;">
                <i class="fa-solid fa-chart-line text-success me-2"></i> Business Profit & Stock Prediction
            </h4>
            <p class="text-muted small mb-0" style="font-size: 0.76rem;">
                AI & Trend-Based Financial Forecasting & Stock Restock Planning
            </p>
        </div>
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="badge bg-dark text-warning border border-warning px-2.5 py-1.5 rounded-pill" style="font-size: 0.74rem;">
                <i class="fa-solid fa-calendar-days me-1"></i> Target: {{ $targetDate->format('d M Y') }} ({{ $daysToTarget }} days)
            </span>
        </div>
    </div>
</div>

<!-- TOP CONTROLS & BUNDLE CONFIGURATION CARD -->
<div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 bg-white">
    <div class="card-header bg-dark text-white rounded-top-4 py-2.5 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="font-size: 0.88rem;">
            <i class="fa-solid fa-sliders text-warning me-2"></i> Prediction Settings & Restock Parameters
        </h6>
        <button class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;" type="button" data-bs-toggle="collapse" data-bs-target="#predictionConfigCollapse" aria-expanded="true">
            <i class="fa-solid fa-chevron-down me-1"></i> Quick Adjust
        </button>
    </div>
    <div class="card-body p-3 p-md-4 collapse show" id="predictionConfigCollapse">
        <form action="{{ route('admin.profit-prediction.index') }}" method="GET" id="predictionForm">
            <div class="row g-3">
                <!-- Target Date Selection -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-dark mb-1">
                        <i class="fa-solid fa-calendar text-primary me-1"></i> Target Future Date
                    </label>
                    <input type="date" name="target_date" class="form-control rounded-3 py-1.5" style="font-size: 0.85rem;" value="{{ $targetDateInput }}" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" onchange="document.getElementById('predictionForm').submit()">
                    
                    <!-- Quick Presets -->
                    <div class="d-flex gap-1.5 mt-2 flex-wrap">
                        <button type="button" onclick="setTargetDays(15)" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" style="font-size: 0.68rem;">+15 Days</button>
                        <button type="button" onclick="setTargetDays(30)" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" style="font-size: 0.68rem;">+30 Days</button>
                        <button type="button" onclick="setTargetDays(60)" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" style="font-size: 0.68rem;">+60 Days</button>
                        <button type="button" onclick="setTargetDays(90)" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" style="font-size: 0.68rem;">+90 Days</button>
                    </div>
                </div>

                <!-- Bundle Restock Rate & Weight -->
                <div class="col-6 col-md-2.5">
                    <label class="form-label fw-bold small text-dark mb-1">
                        <i class="fa-solid fa-weight-hanging text-warning me-1"></i> Bundle Weight (kg)
                    </label>
                    <input type="number" step="1" name="bundle_weight_kg" class="form-control rounded-3 py-1.5" style="font-size: 0.85rem;" value="{{ $bundleWeightKg }}">
                </div>

                <div class="col-6 col-md-2.5">
                    <label class="form-label fw-bold small text-dark mb-1">
                        <i class="fa-solid fa-indian-rupee-sign text-success me-1"></i> Rate per kg (₹)
                    </label>
                    <input type="number" step="1" name="bundle_rate_per_kg" class="form-control rounded-3 py-1.5" style="font-size: 0.85rem;" value="{{ $bundleRatePerKg }}">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold small text-dark mb-1">
                        <i class="fa-solid fa-shirt text-info me-1"></i> Yield Pcs / Bundle
                    </label>
                    <input type="number" step="5" name="bundle_yield_pcs" class="form-control rounded-3 py-1.5" style="font-size: 0.85rem;" value="{{ $bundleYieldPcs }}" placeholder="e.g. 380">
                </div>

                <!-- Manual Velocity Overrides (Optional) -->
                <div class="col-12 border-top pt-2 mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold" style="font-size: 0.74rem;">
                            <i class="fa-solid fa-gauge-high me-1 text-secondary"></i> Sales Velocity Speed Tuning (Optional Override)
                        </span>
                        <button type="submit" class="btn btn-warning rounded-pill btn-sm fw-bold px-3 py-1 shadow-sm text-dark" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Recalculate Prediction
                        </button>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label text-muted small mb-0" style="font-size: 0.7rem;">Est. Daily Pieces Sold</label>
                    <input type="number" step="0.5" name="manual_daily_pieces" class="form-control rounded-3 py-1" style="font-size: 0.8rem;" placeholder="Historical avg: {{ number_format($avgDailyPieces, 1) }} pcs/day" value="{{ request('manual_daily_pieces') }}">
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label text-muted small mb-0" style="font-size: 0.7rem;">Est. Avg Selling Price / Pc (₹)</label>
                    <input type="number" step="1" name="manual_price_per_piece" class="form-control rounded-3 py-1" style="font-size: 0.8rem;" placeholder="Historical avg: ₹{{ number_format($avgPricePerPiece, 0) }}" value="{{ request('manual_price_per_piece') }}">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small mb-0" style="font-size: 0.7rem;">Est. Daily Operating Expense (₹)</label>
                    <input type="number" step="10" name="manual_daily_expense" class="form-control rounded-3 py-1" style="font-size: 0.8rem;" placeholder="Historical avg: ₹{{ number_format($avgDailyExpense, 0) }}" value="{{ request('manual_daily_expense') }}">
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KEY FINANCIAL PREDICTION CARDS (4-GRID) -->
<div class="row g-2.5 g-md-3 mb-3 mb-md-4">
    <!-- Projected Gross Revenue -->
    <div class="col-6 col-xl-3">
        <div class="card kpi-card bg-white shadow-sm p-2.5 p-md-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="text-muted text-uppercase fw-bold kpi-title">Gross Revenue</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">
                    {{ number_format($projectedTotalPiecesSold) }} pcs
                </span>
            </div>
            <div class="fw-bold text-success kpi-val fs-4 mb-0">₹{{ number_format($projectedGrossRevenue, 0) }}</div>
            <div class="text-muted kpi-sub mt-1">
                Avg ₹{{ number_format($projectedPricePerPiece, 0) }}/pc &bull; ~{{ number_format($projectedTotalOrders) }} orders
            </div>
        </div>
    </div>

    <!-- Projected Restock Investment -->
    <div class="col-6 col-xl-3">
        <div class="card kpi-card bg-white shadow-sm p-2.5 p-md-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="text-muted text-uppercase fw-bold kpi-title">Restock Investment</span>
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill" style="font-size: 0.65rem;">
                    {{ $bundlesRequired }} {{ Str::plural('Bundle', $bundlesRequired) }}
                </span>
            </div>
            <div class="fw-bold text-dark kpi-val fs-4 mb-0">₹{{ number_format($totalRestockExpense, 0) }}</div>
            <div class="text-muted kpi-sub mt-1">
                {{ $bundlesRequired }} &times; {{ $bundleWeightKg }}kg @ ₹{{ $bundleRatePerKg }}/kg (₹{{ number_format($bundleCost, 0) }}/bndl)
            </div>
        </div>
    </div>

    <!-- Projected Daily Operational Expenses -->
    <div class="col-6 col-xl-3">
        <div class="card kpi-card bg-white shadow-sm p-2.5 p-md-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="text-muted text-uppercase fw-bold kpi-title">Daily Expenses</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.65rem;">
                    {{ $daysToTarget }} Days
                </span>
            </div>
            <div class="fw-bold text-danger kpi-val fs-4 mb-0">₹{{ number_format($totalProjectedDailyExpenses, 0) }}</div>
            <div class="text-muted kpi-sub mt-1">
                ₹{{ number_format($projectedDailyExpense, 0) }}/day operational costs
            </div>
        </div>
    </div>

    <!-- Projected Net Profit / Loss -->
    <div class="col-6 col-xl-3">
        <div class="card kpi-card bg-dark text-white shadow-sm p-2.5 p-md-3 border-start border-4 border-{{ $projectedNetProfit >= 0 ? 'success' : 'danger' }}">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="text-light text-uppercase fw-bold kpi-title">Net Profit Forecast</span>
                <span class="badge bg-{{ $projectedNetProfit >= 0 ? 'success' : 'danger' }} rounded-pill" style="font-size: 0.65rem;">
                    {{ number_format($profitMarginPercent, 1) }}% Margin
                </span>
            </div>
            <div class="fw-bold text-{{ $projectedNetProfit >= 0 ? 'warning' : 'danger' }} kpi-val fs-4 mb-0">
                ₹{{ number_format($projectedNetProfit, 0) }}
            </div>
            <div class="text-light opacity-75 kpi-sub mt-1">
                Net Profit after Restock & Operating Costs
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4">
    <!-- LEFT: INVENTORY RESTOCK TIMELINE MILESTONES -->
    <div class="col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                    <i class="fa-solid fa-boxes-packing text-warning me-2"></i> Stock Restock Purchase Milestones
                </h6>
                <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">
                    Current Shop Stock: <strong>{{ number_format($totalCurrentAvailableStock) }} Pcs</strong>
                </span>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.78rem;">
                        <span class="text-muted">Target Sales Volume:</span>
                        <span class="fw-bold text-dark">{{ number_format($projectedTotalPiecesSold) }} Pieces</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.78rem;">
                        <span class="text-muted">Available Stock Covered:</span>
                        <span class="fw-bold text-success">{{ number_format(min($totalCurrentAvailableStock, $projectedTotalPiecesSold)) }} Pieces</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="font-size: 0.78rem;">
                        <span class="text-muted">New Stock Needed:</span>
                        <span class="fw-bold text-danger">{{ number_format(max(0, $projectedTotalPiecesSold - $totalCurrentAvailableStock)) }} Pieces</span>
                    </div>
                </div>

                @if(count($milestones) > 0)
                    <h6 class="fw-bold small text-dark mb-3">Restock Trigger Dates:</h6>
                    <div class="timeline ps-2 border-start border-2 border-warning ms-2">
                        @foreach($milestones as $m)
                            <div class="position-relative mb-3 ps-3">
                                <span class="position-absolute top-0 start-0 translate-middle timeline-dot bg-warning border border-dark"></span>
                                <div class="bg-light p-2.5 rounded-3 border shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 0.82rem;">
                                            <i class="fa-solid fa-box text-warning me-1"></i> Bundle Restock #{{ $m['bundle_num'] }} ({{ $bundleWeightKg }} kg)
                                        </span>
                                        <span class="badge bg-dark text-warning" style="font-size: 0.65rem;">
                                            ₹{{ number_format($m['cost']) }}
                                        </span>
                                    </div>
                                    <div class="text-secondary small" style="font-size: 0.75rem;">
                                        Estimated Trigger Date: <strong class="text-dark">{{ $m['estimated_date'] }}</strong> (Day {{ $m['days_from_now'] }})
                                    </div>
                                    <div class="text-muted extra-small mt-0.5" style="font-size: 0.68rem;">
                                        Triggered after selling approx ~{{ number_format($m['pieces_sold_at_trigger']) }} total pieces.
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success border-success rounded-3 p-3 text-center mb-0" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-check text-success fs-5 d-block mb-1"></i>
                        <strong>No New Restock Required!</strong><br>
                        Your current shop inventory of <strong>{{ $totalCurrentAvailableStock }} pcs</strong> is sufficient to cover the projected {{ $projectedTotalPiecesSold }} pcs sales up to {{ $targetDate->format('d M Y') }}.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT: DETAILED FINANCIAL BREAKDOWN TABLE -->
    <div class="col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                    <i class="fa-solid fa-calculator text-primary me-2"></i> Financial Model Breakdown
                </h6>
            </div>
            <div class="card-body p-3 p-md-4" style="font-size: 0.78rem;">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <td class="text-muted">Target Time Period:</td>
                            <td class="text-end fw-bold text-dark">{{ $daysToTarget }} Days</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Historical Daily Orders:</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($avgDailyOrders, 1) }} / day</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Daily Sales Velocity:</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($projectedDailyPieces, 1) }} pcs/day</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Avg Price per Piece:</td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($projectedPricePerPiece, 0) }}</td>
                        </tr>
                        <tr class="border-bottom table-light">
                            <td class="fw-bold text-dark">Projected Gross Revenue:</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($projectedGrossRevenue, 0) }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Bundle Rate ({{ $bundleWeightKg }} kg @ ₹{{ $bundleRatePerKg }}/kg):</td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($bundleCost, 0) }} / bundle</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Bundles Required ({{ $bundleYieldPcs }} pcs/bndl):</td>
                            <td class="text-end fw-bold text-dark">{{ $bundlesRequired }} Bundles</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Total Stock Purchase Cost:</td>
                            <td class="text-end fw-bold text-warning">₹{{ number_format($totalRestockExpense, 0) }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted">Projected Operational Expenses:</td>
                            <td class="text-end fw-bold text-danger">₹{{ number_format($totalProjectedDailyExpenses, 0) }}</td>
                        </tr>
                        <tr class="border-bottom table-light">
                            <td class="fw-bold text-dark">Total Expenses (Restock + Ops):</td>
                            <td class="text-end fw-bold text-danger">₹{{ number_format($totalProjectedExpenses, 0) }}</td>
                        </tr>
                        <tr class="table-dark text-white rounded-3">
                            <td class="fw-bold p-2 text-warning fs-6">Projected Net Profit:</td>
                            <td class="text-end fw-bold p-2 text-warning fs-6">₹{{ number_format($projectedNetProfit, 0) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-info border-info p-2.5 rounded-3 mt-3 mb-0" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Note:</strong> Predictions are computed dynamically using your last 30 days of actual sales velocity and expense data. You can override parameters anytime in the top settings box.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setTargetDays(days) {
    const today = new Date();
    today.setDate(today.getDate() + days);
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const formatted = `${yyyy}-${mm}-${dd}`;
    
    const dateInput = document.querySelector('input[name="target_date"]');
    if (dateInput) {
        dateInput.value = formatted;
        document.getElementById('predictionForm').submit();
    }
}
</script>
@endsection
