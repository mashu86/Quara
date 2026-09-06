@extends('layouts.admin')

@section('title', ($draw->title ?: $draw->draw_number) . ' - Lucky Winner Details')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2.5 px-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-trophy text-warning me-2"></i>
            <span>{{ $draw->display_title }}</span>
        </h3>
        <p class="text-muted small mb-0">Lucky Winner Details &middot; {{ $draw->period_label }} (Ref #{{ $draw->draw_number }})</p>
    </div>
    <a href="{{ route('admin.luckywinner.history') }}" class="btn btn-outline-dark rounded-pill px-3 fw-semibold">
        <i class="fa-solid fa-arrow-left me-2"></i>Lucky Winner History
    </a>
</div>

<!-- EDIT CUSTOM DRAW TITLE / NAME CARD -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FFFDF7; border: 1.5px dashed var(--qw-gold, #D4AF37) !important;">
    <div class="card-body p-3 p-md-4">
        <form action="{{ route('admin.luckywinner.update-title', $draw->id) }}" method="POST" id="updateTitleForm">
            @csrf
            <div class="row align-items-center g-2 g-md-3">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">
                        <i class="fa-solid fa-pen-to-square text-warning me-1"></i> Custom Draw Title / Name
                    </label>
                    <div class="form-text small text-muted" style="font-size: 0.73rem;">Set a custom title for this history record.</div>
                </div>
                <div class="col-12 col-md-6">
                    <input type="text" name="title" class="form-control rounded-3 py-2 fw-semibold bg-white" placeholder="e.g. Onam Mega Contest / September Giveaway" value="{{ old('title', $draw->title) }}">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-warning text-dark rounded-pill fw-bold w-100 py-2 shadow-xs" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Draw Name
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach(['Successful Orders' => $draw->total_successful_orders, 'Eligible Entries' => $draw->total_entries, 'Gifts' => $draw->gift_count, 'Winners' => $draw->winner_count] as $label => $count)
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-3">
            <span class="text-muted small">{{ $label }}</span><h3 class="fw-bold mt-2 mb-0">{{ $count }}</h3>
        </div></div></div>
    @endforeach
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 px-4"><h6 class="fw-bold mb-0">Draw Details</h6></div>
    <div class="card-body p-4">
        <dl class="row g-3 mb-0">
            @foreach([
                'Custom Draw Title' => $draw->title ?: 'Not set (Using Period Label)',
                'Draw Type' => $draw->draw_type === 'month' ? 'Month' : 'Date range',
                'Selected Period' => $draw->period_label,
                'Start Date' => $draw->start_date->format('d M Y'),
                'End Date' => $draw->end_date->format('d M Y'),
                'Selected Month' => $draw->selected_month ?? 'Custom date range',
                'Draw Date / Time' => $draw->drawn_at->format('d M Y, h:i:s A'),
                'Stored At' => $draw->created_at->format('d M Y, h:i:s A'),
                'Hosted By' => $draw->admin_name,
                'Time Zone' => $draw->timezone,
                'Eligibility Checked At' => $draw->eligibility_checked_at->format('d M Y, h:i:s A'),
                'Selection Weights' => 'Normal: ' . $draw->selection_rules['normal_weight'] . ' / Return: ' . $draw->selection_rules['return_weight'],
            ] as $label => $value)
                <div class="col-12 col-sm-6 col-xl-4"><dt class="text-muted small fw-normal">{{ $label }}</dt><dd class="fw-semibold mb-0 mt-1 text-break">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>

<h5 class="fw-bold mb-3">Gift Winners</h5>
<div class="row g-4">
    @foreach($draw->winners as $winner)
        <div class="col-12 col-xl-6">
            <article class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white d-flex align-items-center gap-3 px-4 py-3">
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Winner {{ $winner->position }}</span>
                    <h6 class="fw-bold mb-0 text-break">{{ $winner->customer_name }}</h6>
                </div>
                <div class="card-body p-4">
                    <dl class="row g-3 mb-0">
                        <div class="col-sm-6"><dt class="text-muted small fw-normal">Order Number / ID</dt><dd class="mb-0 text-break">{{ $winner->order_number }} / {{ $winner->order_id }}</dd></div>
                        <div class="col-sm-6"><dt class="text-muted small fw-normal">Order Date</dt><dd class="mb-0">{{ $winner->order_date->format('d M Y, h:i A') }}</dd></div>
                        <div class="col-sm-6"><dt class="text-muted small fw-normal">Order Type</dt><dd class="mb-0">{{ ucfirst($winner->order_type) }}</dd></div>
                        <div class="col-sm-6"><dt class="text-muted small fw-normal">Selected At</dt><dd class="mb-0">{{ $winner->selected_at->format('d M Y, h:i:s A') }}</dd></div>
                        <div class="col-12"><dt class="text-muted small fw-normal">Customer Address</dt><dd class="mb-0 text-break">{{ $winner->customer_address ?: 'No address recorded' }}</dd></div>
                        <div class="col-12"><dt class="text-muted small fw-normal">Eligibility at Draw</dt><dd class="mb-0">{{ ucfirst($winner->eligibility['payment_status']) }} &middot; {{ ucfirst($winner->eligibility['order_status']) }}</dd></div>
                        <div class="col-12"><dt class="text-muted small fw-normal">Return / Winning Weight</dt><dd class="mb-0">{{ $winner->eligibility['has_return'] ? 'Reduced weight: return activity in selected period' : 'Normal weight: no matching return activity in selected period' }} ({{ $winner->eligibility['weight'] }})</dd></div>
                    </dl>
                    @if($winner->eligibility['has_return'])
                        <ul class="small text-muted mt-3 mb-0 ps-3">
                            @foreach($winner->eligibility['return_activity'] as $activity)
                                <li>{{ ucwords(str_replace('_', ' ', $activity['type'])) }} &middot; {{ $activity['date'] }} &middot; Order ID {{ $activity['order_id'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>
        </div>
    @endforeach
</div>
@endsection
