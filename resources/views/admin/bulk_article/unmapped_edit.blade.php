@extends('layouts.admin')
@section('title','Map with Order ID')
@section('content')
<style>
    .um-results{max-height:calc(100vh - 300px);min-height:200px;overflow-y:auto}
    .um-card{border:1px solid #dee2e6;border-radius:.5rem;padding:.6rem .8rem;margin-bottom:.5rem;cursor:pointer;background:#fff}
    .um-card:hover{border-color:#0d6efd}
    .um-card.is-picked{border-color:#198754;background:#f1faf4;box-shadow:0 0 0 .15rem rgba(25,135,84,.25)}
    .um-card small{display:block;color:#6c757d}
</style>
<div class="container-fluid py-3">
    <h3>Map with Order ID</h3>
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Excel Row</div>
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-sm-6"><span class="text-muted">Receiver</span><div class="fw-semibold">{{ $article->receiver_name }}</div></div>
                        <div class="col-sm-6"><span class="text-muted">Article / Tracking</span><div class="fw-semibold">{{ $article->article_number }} / {{ $article->tracking_number ?: '—' }}</div></div>
                        <div class="col-12"><span class="text-muted">Address</span><div>{{ $article->receiver_address }}{{ $article->pin_code ? ' - '.$article->pin_code : '' }}</div></div>
                        <div class="col-sm-6"><span class="text-muted">Phone</span><div class="fw-semibold">{{ $article->receiver_phone ?: '—' }}</div></div>
                        <div class="col-sm-6"><span class="text-muted">Booking Date</span><div class="fw-semibold">{{ $article->booking_date ?: '—' }}</div></div>
                        <div class="col-sm-6"><span class="text-muted">PIN</span><div>{{ $article->pin_code ?: '—' }}</div></div>
                        <div class="col-sm-6"><span class="text-muted">Tariff</span><div>₹{{ number_format((float) $article->courier_charge, 2) }}</div></div>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('admin.bulk-article.unmapped.connect', $article) }}" class="card"><div class="card-body">
                @csrf @method('PUT')
                <label class="form-label">Order ID</label>
                <input name="order_number" id="umOrderNumber" value="{{ old('order_number') }}" class="form-control @error('order_number') is-invalid @enderror" placeholder="Pick an order on the right or type the order ID" required>
                @error('order_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">The shipment is saved with this article number, the tariff and the website weight of the order.</div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('admin.bulk-article.unmapped') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-success">Map to Order</button>
                </div>
            </div></form>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold">Find Order</div>
                <div class="card-body">
                    <input type="search" id="umSearch" class="form-control mb-2" value="{{ $article->receiver_name }}" placeholder="Search by name, order ID, phone or PIN code" autocomplete="off">
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="small text-muted me-1 align-self-center">Quick search:</span>
                        @foreach(array_filter(['Name' => $article->receiver_name, 'Phone' => $article->receiver_phone ? substr(preg_replace('/\D+/', '', $article->receiver_phone), -10) : null, 'PIN' => $article->pin_code]) as $label => $value)
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-quick-search="{{ $value }}">{{ $label }}: {{ $value }}</button>
                        @endforeach
                    </div>
                    <div class="small text-muted mb-2" id="umStatus"></div>
                    <div class="um-results" id="umResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const search = document.getElementById('umSearch'), results = document.getElementById('umResults'), status = document.getElementById('umStatus'), input = document.getElementById('umOrderNumber');
    const url = @json(route('admin.bulk-article.unmapped.search-orders'));
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let timer = null, latest = 0;

    async function run() {
        const term = search.value.trim();
        if (term.length < 2) { results.innerHTML = ''; status.textContent = 'Type at least 2 letters.'; return; }
        const id = ++latest;
        status.textContent = 'Searching…';
        try {
            const res = await fetch(`${url}?q=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (id !== latest) return;
            status.textContent = data.orders.length ? `${data.orders.length} order(s) without tracking. Click one to use its order ID.` : 'No orders without tracking match this search.';
            results.innerHTML = data.orders.map(o => `
                <div class="um-card ${o.order_number === input.value.trim() ? 'is-picked' : ''}" data-order="${esc(o.order_number)}">
                    <div class="d-flex justify-content-between"><strong>${esc(o.name)}</strong><span class="fw-semibold text-primary">${esc(o.order_number)}</span></div>
                    <small>${esc(o.address)}</small>
                    <small>${esc(o.phone)} · ${esc(o.date)} · ₹${esc(o.amount)} · ${esc(o.weight)} g</small>
                </div>`).join('');
        } catch (e) {
            if (id === latest) status.textContent = 'Search failed. Please try again.';
        }
    }

    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(run, 250); });
    document.querySelectorAll('[data-quick-search]').forEach(b => b.addEventListener('click', () => { search.value = b.dataset.quickSearch; run(); }));
    results.addEventListener('click', e => {
        const card = e.target.closest('.um-card');
        if (!card) return;
        input.value = card.dataset.order;
        input.classList.remove('is-invalid');
        results.querySelectorAll('.um-card').forEach(c => c.classList.toggle('is-picked', c === card));
    });
    run();
})();
</script>
@endsection
