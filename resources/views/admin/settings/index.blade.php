@extends('layouts.admin')

@section('title', 'Payment Gateway Settings - QUARA WALDROP Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .settings-header-title {
            font-size: 1.15rem !important;
        }
        .settings-header-subtitle {
            font-size: 0.72rem !important;
        }
        .settings-top-btn {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.75rem !important;
        }
        .card-body.p-4 {
            padding: 1rem 0.85rem !important;
        }
        .card-header h5 {
            font-size: 0.88rem !important;
        }
        .form-label {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .form-control, .input-group-text {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .form-check-label {
            font-size: 0.74rem !important;
        }
        .save-settings-btn {
            font-size: 0.82rem !important;
            padding: 0.55rem 1rem !important;
        }
        .preview-box {
            font-size: 0.74rem !important;
            padding: 0.75rem !important;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 settings-header-title"><i class="fa-solid fa-sliders text-warning me-2"></i> Payment Gateway & System Settings</h3>
        <p class="text-muted small mb-0 settings-header-subtitle">Configure Razorpay payment gateway fees, GST rates, and financial calculation settings.</p>
    </div>
    <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 settings-top-btn shadow-sm w-100 w-sm-auto text-center fw-bold" title="View Profit & Loss">
        <i class="fa-solid fa-chart-pie me-1"></i> View Profit & Loss
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3 g-md-4">
    <div class="col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-brands fa-credit-card text-success me-2"></i> Razorpay Fee & Tax Configuration
                </h5>
            </div>
            <div class="card-body p-3.5 p-md-4">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Razorpay Base Fee Percentage (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="razorpay_fee_percent" class="form-control rounded-start-3" value="{{ old('razorpay_fee_percent', $razorpayFeePercent) }}" required>
                            <span class="input-group-text bg-light fw-bold">%</span>
                        </div>
                        <div class="form-text extra-small text-muted">Standard Razorpay gateway processing fee (Default: 2.00%).</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">GST on Razorpay Fee (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="razorpay_gst_percent" class="form-control rounded-start-3" value="{{ old('razorpay_gst_percent', $razorpayGstPercent) }}" required>
                            <span class="input-group-text bg-light fw-bold">% GST</span>
                        </div>
                        <div class="form-text extra-small text-muted">Government GST applied on the Razorpay base fee (Default: 18.00%).</div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="recalculate_past_orders" id="recalculatePastOrders" value="1">
                            <label class="form-check-label fw-bold text-dark" for="recalculatePastOrders">
                                Recalculate past online orders with these updated fee rates
                            </label>
                        </div>
                        <div class="extra-small text-muted mt-1">If checked, all previous online orders will be updated to reflect the new Razorpay fee and GST rates.</div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark w-100 w-sm-auto save-settings-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm bg-light">
            <div class="card-body p-3.5 p-md-4">
                <h6 class="fw-bold text-dark mb-2 mb-md-3"><i class="fa-solid fa-calculator text-primary me-2"></i> Calculation Formula Preview</h6>
                <p class="small text-secondary mb-3">Here is how Razorpay expenses are automatically calculated per online payment:</p>

                <div class="bg-white p-3 rounded-3 border mb-3 small preview-box">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Payment Amount:</span>
                        <strong>₹100.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-muted">
                        <span>Base Fee ({{ $razorpayFeePercent }}%):</span>
                        <span>₹{{ number_format(100 * ($razorpayFeePercent / 100), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-muted">
                        <span>GST on Fee ({{ $razorpayGstPercent }}%):</span>
                        <span>₹{{ number_format((100 * ($razorpayFeePercent / 100)) * ($razorpayGstPercent / 100), 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold text-danger mb-1">
                        <span>Total Razorpay Expense:</span>
                        <span>₹{{ number_format((100 * ($razorpayFeePercent / 100)) * (1 + ($razorpayGstPercent / 100)), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold text-success">
                        <span>Net Amount Received:</span>
                        <span>₹{{ number_format(100 - ((100 * ($razorpayFeePercent / 100)) * (1 + ($razorpayGstPercent / 100))), 2) }}</span>
                    </div>
                </div>

                <div class="extra-small text-muted">
                    <i class="fa-solid fa-info-circle me-1"></i> These charges are automatically deducted as an expense item in the Profit & Loss statement for all online orders.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
