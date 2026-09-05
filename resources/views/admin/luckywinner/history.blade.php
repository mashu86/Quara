@extends('layouts.admin')

@section('title', 'Lucky Winner History - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-trophy text-warning me-2"></i>Lucky Winner History</h3>
        <p class="text-muted small mb-0">Saved giveaway draws and the customers who won gifts.</p>
    </div>
    <a href="{{ route('luckywinner.index') }}" class="btn btn-warning rounded-pill fw-semibold px-4">
        <i class="fa-solid fa-gift me-2"></i>Open Lucky Draw
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-bold mb-0">Stored Draws</h6>
        <span class="badge bg-dark rounded-pill">{{ $draws->total() }} {{ Str::plural('draw', $draws->total()) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th class="ps-4">Draw ID / Date</th>
                    <th>Selected Period</th>
                    <th class="text-center">Successful Orders / Entries</th>
                    <th class="text-center">Gifts / Winners</th>
                    <th>Winner Names</th>
                    <th class="pe-4 text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($draws as $draw)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('admin.luckywinner.show', $draw) }}" class="fw-bold text-dark text-nowrap">{{ $draw->draw_number }}</a>
                            <div class="text-muted small mt-1">{{ $draw->drawn_at->format('d M Y, h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $draw->period_label }}</div>
                            <span class="text-muted small">{{ $draw->draw_type === 'month' ? 'Month' : 'Date range' }}</span>
                        </td>
                        <td class="text-center">{{ $draw->total_successful_orders }} / {{ $draw->total_entries }}</td>
                        <td class="text-center">{{ $draw->gift_count }} / {{ $draw->winner_count }}</td>
                        <td>
                            <div style="max-height: 120px; overflow-y: auto; min-width: 160px;">
                                @foreach($draw->winners as $winner)
                                    <div class="small py-1"><span class="badge bg-warning text-dark me-1">{{ $winner->position }}</span> {{ $winner->customer_name }}</div>
                                @endforeach
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.luckywinner.show', $draw) }}" class="btn btn-sm btn-outline-dark rounded-pill text-nowrap">
                                <i class="fa-solid fa-eye me-1"></i> View Winners
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 px-3">
                        <i class="fa-solid fa-trophy fa-2x text-warning mb-3"></i>
                        <h6 class="fw-bold">No saved draws yet</h6>
                        <p class="text-muted small mb-0">After revealing all winners in Lucky Draw, click <strong>Store Winners</strong>. They will appear here.</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($draws->hasPages())
        <div class="card-footer bg-white px-4 pt-3">{{ $draws->links() }}</div>
    @endif
</div>
@endsection
