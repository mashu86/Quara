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

<!-- SECTION: CLIENT SITE VISIBILITY CONTROL PANEL -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #111111 0%, #222222 100%); color: #fff;">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1" style="background-color: var(--qw-gold) !important;">
                        <i class="fa-solid fa-eye me-1"></i> Client Site Banner
                    </span>
                    <h5 class="fw-bold text-white mb-0 font-serif fs-6 fs-md-5">Last Lucky Draw User Visibility</h5>
                </div>
                <p class="text-white-50 small mb-0">Control whether the latest lucky draw winners are advertised to customers on the website header.</p>
            </div>

            <div class="d-flex align-items-center gap-3">
                @if(($totalDrawsCount ?? 0) === 0)
                    <div class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 small fw-semibold">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> ഇതുവരെ Lucky Draw ഒന്നും എടുത്തിട്ടില്ല
                    </div>
                @else
                    <div class="form-check form-switch fs-5 mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="luckyWinnerVisibilityToggle" role="switch" {{ ($showLastLuckyDraw ?? '0') === '1' ? 'checked' : '' }} style="cursor: pointer; width: 45px; height: 24px;">
                        <label class="form-check-label text-white small fw-bold mb-0" for="luckyWinnerVisibilityToggle" id="toggleLabel" style="cursor: pointer;">
                            {{ ($showLastLuckyDraw ?? '0') === '1' ? 'VISIBLE (ON)' : 'HIDDEN (OFF)' }}
                        </label>
                    </div>
                @endif
            </div>
        </div>
        <div id="visibilityAlertContainer" class="mt-2"></div>
    </div>
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
                            @if($draw->title)
                                <div class="fw-bold text-dark mb-0.5" style="font-size: 0.95rem;">
                                    <i class="fa-solid fa-trophy text-warning me-1 small"></i>{{ $draw->title }}
                                </div>
                                <div class="small text-muted mb-1" style="font-size: 0.76rem;">{{ $draw->period_label }}</div>
                            @else
                                <div class="fw-semibold text-dark">{{ $draw->period_label }}</div>
                            @endif
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small">
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
                            <div class="d-flex flex-column align-items-end gap-2">
                                <a href="{{ route('admin.luckywinner.show', $draw) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold text-nowrap">
                                    <i class="fa-solid fa-eye me-1"></i> View Details
                                </a>
                                <form action="{{ route('admin.luckywinner.destroy', $draw) }}" method="POST" onsubmit="return confirm('Delete this draw and its winner history? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold text-nowrap" aria-label="Delete draw {{ $draw->draw_number }}">
                                        <i class="fa-solid fa-trash me-1" aria-hidden="true"></i> Delete
                                    </button>
                                </form>
                            </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.getElementById('luckyWinnerVisibilityToggle');
    const toggleLabel = document.getElementById('toggleLabel');
    const container = document.getElementById('visibilityAlertContainer');

    if (!toggleSwitch) return;

    toggleSwitch.addEventListener('change', function() {
        const isChecked = this.checked;
        const statusVal = isChecked ? '1' : '0';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Optimistic UI label update
        toggleLabel.textContent = isChecked ? 'VISIBLE (ON)' : 'HIDDEN (OFF)';

        fetch('{{ route("admin.luckywinner.toggle-visibility") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ show_last_lucky_draw: statusVal })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 px-3 mb-0" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i> ${data.message}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                `;
            } else {
                toggleSwitch.checked = !isChecked;
                toggleLabel.textContent = !isChecked ? 'VISIBLE (ON)' : 'HIDDEN (OFF)';
                container.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 mb-0" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.message}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        })
        .catch(err => {
            toggleSwitch.checked = !isChecked;
            toggleLabel.textContent = !isChecked ? 'VISIBLE (ON)' : 'HIDDEN (OFF)';
            container.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 mb-0" role="alert">
                    <i class="fa-solid fa-circle-xmark me-1"></i> Failed to update setting. Please try again.
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            `;
        });
    });
});
</script>
@endsection
