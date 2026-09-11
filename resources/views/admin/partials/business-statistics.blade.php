<div class="card border-0 rounded-4 shadow-sm mb-4" id="business-statistics">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h4 class="fs-5 fw-bold mb-1">Business Statistics</h4>
                <p class="small text-muted mb-0">Daily performance in rupees. Revenue (profit) = Sales &minus; Expenses.</p>
            </div>
            <div class="border rounded-3 p-3 bg-light">
                <div class="small text-muted">Average Daily Sale</div>
                <div class="fs-4 fw-bold text-primary">&#8377;{{ number_format($businessStats['averageSales'], 2) }}</div>
                <div class="small text-muted">&#8377;{{ number_format($businessStats['totalSales'], 2) }} / {{ number_format($businessStats['businessDays']) }} calendar days</div>
                <div class="small text-muted">{{ \Illuminate\Support\Carbon::parse($businessStats['businessStart'])->format('d M Y') }} &ndash; today (both included)</div>
            </div>
        </div>
        <form action="{{ route('admin.dashboard') }}#business-statistics" method="GET" id="business-statistics-form">
            <input type="hidden" name="metrics_submitted" value="1">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="statistics-period" class="form-label small fw-semibold">Period</label>
                    <select id="statistics-period" name="period" class="form-select">
                        @foreach(['today' => 'Today', 'all' => 'All (business start to today)', 'week' => 'This Week', 'month' => 'This Month', 'range' => 'Date Range'] as $value => $label)
                            <option value="{{ $value }}" @selected($businessStats['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3" data-statistics-range @if($businessStats['period'] !== 'range') hidden @endif>
                    <label for="statistics-start" class="form-label small fw-semibold">From</label>
                    <input type="date" class="form-control" id="statistics-start" name="start_date" value="{{ max($businessStats['startDate'], $businessStats['businessStart']) }}" min="{{ $businessStats['businessStart'] }}" max="{{ $businessStats['today'] }}" @disabled($businessStats['period'] !== 'range') required>
                </div>
                <div class="col-6 col-sm-3" data-statistics-range @if($businessStats['period'] !== 'range') hidden @endif>
                    <label for="statistics-end" class="form-label small fw-semibold">To</label>
                    <input type="date" class="form-control" id="statistics-end" name="end_date" value="{{ $businessStats['endDate'] }}" max="{{ $businessStats['today'] }}" @disabled($businessStats['period'] !== 'range') required>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-dark">Apply</button></div>
            </div>
            <fieldset class="d-flex flex-wrap gap-4 mb-2">
                <legend class="small fw-semibold float-none w-auto mb-2">Show on graph</legend>
                @foreach(['sales' => ['Sales', '#2563eb'], 'expense' => ['Expense', '#dc2626'], 'revenue' => ['Revenue', '#16a34a']] as $metric => [$label, $color])
                    <label class="d-flex align-items-center gap-2 fw-semibold" style="color: {{ $color }}; cursor: pointer;">
                        <input type="checkbox" name="metrics[]" value="{{ $metric }}" data-statistics-metric style="accent-color: {{ $color }}; width: 18px; height: 18px;" @checked(in_array($metric, $selectedMetrics, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </fieldset>
        </form>
        <p class="small text-muted mb-2">{{ \Illuminate\Support\Carbon::parse($businessStats['startDate'])->format('d M Y') }} &ndash; {{ \Illuminate\Support\Carbon::parse($businessStats['endDate'])->format('d M Y') }} &middot; Week starts Monday.</p>
        <div class="overflow-auto border rounded-3 bg-white">
            <svg id="business-statistics-chart" viewBox="0 0 960 320" style="display: block; width: 100%; min-width: 640px;" role="img" aria-label="Daily sales, expense and revenue line graph"></svg>
        </div>
        <p id="business-statistics-detail" class="small text-muted mt-2 mb-3" aria-live="polite">Point to a day on the graph for its amounts, or open daily figures below.</p>
        <noscript><p class="small text-muted">Enable JavaScript for the graph. Daily figures are available below.</p></noscript>
        <div class="row g-3 mb-3">
            @foreach(['highest' => 'Highest Sales Day', 'lowest' => 'Lowest Sales Day'] as $key => $label)
                <div class="col-12 col-sm-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">{{ $label }}</div>
                        @if($businessStats[$key])
                            <div class="fw-bold fs-5">&#8377;{{ number_format($businessStats[$key]['sales'], 2) }}</div>
                            <div class="small">{{ \Illuminate\Support\Carbon::parse($businessStats[$key]['date'])->format('d M Y') }}
                                @if($businessStats[$key . 'Count'] > 1)
                                    <span class="text-muted">(and {{ $businessStats[$key . 'Count'] - 1 }} other {{ $businessStats[$key . 'Count'] === 2 ? 'day' : 'days' }} tied)</span>
                                @endif
                            </div>
                        @else
                            <div class="small">No business days in this period.</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="small text-muted mb-2">Sales are paid/completed, non-cancelled online and offline orders, before refunds. Expenses include product costs, payment fees, general expenses, active operation costs and refunds. Zero-sale days count towards the average and lowest sales day. Business started on {{ \Illuminate\Support\Carbon::parse($businessStats['businessStart'])->format('d M Y') }}.</p>
        <details>
            <summary class="small fw-semibold">Daily figures</summary>
            <div class="table-responsive mt-2" style="max-height: 320px;">
                <table class="table table-sm small mb-0">
                    <thead><tr><th scope="col">Date</th><th scope="col" class="text-end text-primary">Sales</th><th scope="col" class="text-end text-danger">Expense</th><th scope="col" class="text-end text-success">Revenue</th></tr></thead>
                    <tbody>
                        @foreach($businessStats['days'] as $day)
                            <tr><th scope="row">{{ $day['date'] }}</th><td class="text-end">&#8377;{{ number_format($day['sales'], 2) }}</td><td class="text-end">&#8377;{{ number_format($day['expense'], 2) }}</td><td class="text-end">&#8377;{{ number_format($day['revenue'], 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</div>
<script type="application/json" id="business-statistics-data">{!! json_encode($businessStats['days'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
