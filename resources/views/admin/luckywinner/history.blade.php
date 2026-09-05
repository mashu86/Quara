@extends('layouts.admin')

@section('title', 'Lucky Winner History - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-trophy text-warning me-2"></i>Lucky Winner History</h3>
        <p class="text-muted small mb-0">Saved giveaway draws ordered by date &amp; time with winner details.</p>
    </div>
    <a href="{{ route('luckywinner.index') }}" class="btn btn-warning rounded-pill fw-semibold px-4 shadow-sm">
        <i class="fa-solid fa-gift me-2"></i>Open Lucky Draw
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check text-primary"></i>
            <h6 class="fw-bold mb-0">Stored Lucky Draws</h6>
        </div>
        <span class="badge bg-dark rounded-pill px-3 py-2 fs-7">{{ $draws->total() }} {{ Str::plural('draw', $draws->total()) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase text-muted">
                <tr>
                    <th class="ps-4 py-3" style="min-width: 170px;">Date &amp; Time</th>
                    <th class="py-3" style="min-width: 160px;">Draw Period</th>
                    <th class="py-3 text-center" style="min-width: 140px;">Entries / Winners</th>
                    <th class="py-3" style="min-width: 260px;">Winner Names</th>
                    <th class="pe-4 py-3 text-end" style="min-width: 130px;">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($draws as $draw)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark mb-1">
                                <i class="fa-regular fa-clock me-1 text-primary small"></i>
                                {{ $draw->drawn_at ? $draw->drawn_at->format('d M Y, h:i A') : $draw->created_at->format('d M Y, h:i A') }}
                            </div>
                            <span class="badge bg-light text-secondary border fw-mono small">{{ $draw->draw_number }}</span>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-dark">{{ $draw->period_label }}</div>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small mt-1">
                                <i class="fa-solid {{ $draw->draw_type === 'month' ? 'fa-calendar-days' : 'fa-calendar-range' }} me-1"></i>
                                {{ $draw->draw_type === 'month' ? 'Monthly Draw' : 'Date Range' }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <div class="d-inline-flex flex-column align-items-center">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-bold mb-1">
                                    <i class="fa-solid fa-award me-1"></i> {{ $draw->winner_count }} {{ Str::plural('Winner', $draw->winner_count) }}
                                </span>
                                <small class="text-muted" style="font-size: 11px;">{{ $draw->total_successful_orders }} Eligible Orders</small>
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="d-flex flex-wrap gap-1 align-items-center" style="max-height: 120px; overflow-y: auto; max-width: 320px;">
                                @forelse($draw->winners as $winner)
                                    <span class="badge bg-white text-dark border shadow-sm rounded-pill px-2.5 py-1.5 small d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-warning text-dark rounded-circle px-1.5 py-0.5 small" style="font-size: 10px;">#{{ $winner->position }}</span>
                                        <strong>{{ $winner->customer_name }}</strong>
                                        <small class="text-muted">({{ $winner->order_number }})</small>
                                    </span>
                                @empty
                                    <span class="text-muted small italic">No winners recorded</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('admin.luckywinner.show', $draw) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold text-nowrap">
                                <i class="fa-solid fa-eye me-1"></i> View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 px-3">
                            <div class="my-3">
                                <i class="fa-solid fa-trophy fa-3x text-warning mb-3 opacity-75"></i>
                                <h5 class="fw-bold text-dark">No Saved Lucky Draws Yet</h5>
                                <p class="text-muted small mb-3">Conduct a draw and click <strong>Store Winners</strong> to save the results in history.</p>
                                <a href="{{ route('luckywinner.index') }}" class="btn btn-warning rounded-pill px-4 fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i> Start New Draw
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($draws->hasPages())
        <div class="card-footer bg-white px-4 pt-3 border-top">{{ $draws->links() }}</div>
    @endif
</div>
@endsection
