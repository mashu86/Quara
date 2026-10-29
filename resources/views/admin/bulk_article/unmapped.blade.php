@extends('layouts.admin')
@section('title','Not Mapped Excel Sheet')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Not Mapped Excel Sheet</h3>
        <a href="{{ route('admin.bulk-article.index') }}" class="btn btn-outline-secondary">Back to Bulk Article Number</a>
    </div>

    <div class="card mb-3"><div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-md-4"><label class="form-label">Search</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Receiver, article, tracking or PIN"></div>
            <div class="col-md-2"><button class="btn btn-primary">Search</button></div>
        </form>
    </div></div>

    <div class="card">
        {{-- Mobile cards --}}
        <div class="d-md-none p-2">
            @forelse($articles as $a)
                <div class="border rounded-3 shadow-sm p-2 mb-2 bg-white">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold" style="font-size:.9rem">{{ $a->receiver_name }}</div>
                            @if($a->receiver_phone)<a href="tel:{{ $a->receiver_phone }}" class="text-muted text-decoration-none small"><i class="fa-solid fa-phone text-success me-1"></i>{{ $a->receiver_phone }}</a>@endif
                        </div>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </div>
                    <div class="small text-muted mt-1">{{ $a->receiver_address }}{{ $a->pin_code ? ' - '.$a->pin_code : '' }}</div>
                    <div class="border rounded-3 bg-light p-2 mt-2 small">
                        <div class="d-flex justify-content-between"><span class="text-muted">Article</span><span class="fw-semibold font-monospace">{{ $a->article_number }}</span></div>
                        @if($a->tracking_number && $a->tracking_number !== $a->article_number)
                            <div class="d-flex justify-content-between"><span class="text-muted">Tracking</span><span class="fw-semibold font-monospace">{{ $a->tracking_number }}</span></div>
                        @endif
                    </div>
                    <div class="row g-1 small mt-1">
                        <div class="col-4"><div class="text-muted" style="font-size:.68rem">TARIFF</div>₹{{ number_format((float) $a->courier_charge, 2) }}</div>
                        <div class="col-4"><div class="text-muted" style="font-size:.68rem">BOOKED</div>{{ $a->booking_date ?: '—' }}</div>
                        <div class="col-4"><div class="text-muted" style="font-size:.68rem">SAVED ON</div>{{ $a->created_at?->format('d-m-Y') }}</div>
                    </div>
                    <div class="small mt-1"><span class="text-muted" style="font-size:.68rem">ORDER DATE</span> @if($a->likely_order_date)<span class="fw-semibold">{{ $a->likely_order_date->format('d-m-Y') }}</span> <span class="badge bg-info-subtle text-dark border">{{ (int) $a->likely_order_date->copy()->startOfDay()->diffInDays(now()->startOfDay()) }} days</span><div class="small text-muted">#{{ $a->likely_order_number }}</div>@else<span class="text-muted">—</span>@endif</div>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.bulk-article.unmapped.edit', $a) }}" class="btn btn-sm btn-primary flex-grow-1">Edit / Map with Order ID</a>
                        <form method="post" action="{{ route('admin.bulk-article.unmapped.destroy', $a) }}" onsubmit="return confirm('Delete this not mapped article?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center p-4">No pending Excel rows.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-striped mb-0 align-middle">
                <thead><tr><th>Receiver</th><th>Address</th><th>Article</th><th>Tracking</th><th>Tariff</th><th>Order Date</th><th>Saved On</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($articles as $a)
                    <tr>
                        <td class="fw-semibold">{{ $a->receiver_name }}@if($a->receiver_phone)<div class="small text-muted fw-normal">{{ $a->receiver_phone }}</div>@endif</td>
                        <td class="small">{{ $a->receiver_address }}{{ $a->pin_code ? ' - '.$a->pin_code : '' }}</td>
                        <td>{{ $a->article_number }}</td>
                        <td>{{ $a->tracking_number ?: '—' }}</td>
                        <td>₹{{ number_format((float) $a->courier_charge, 2) }}</td>
                        <td class="text-nowrap">@if($a->likely_order_date)<span class="fw-semibold">{{ $a->likely_order_date->format('d-m-Y') }}</span> <span class="badge bg-info-subtle text-dark border">{{ (int) $a->likely_order_date->copy()->startOfDay()->diffInDays(now()->startOfDay()) }} days</span><div class="small text-muted">#{{ $a->likely_order_number }}</div>@else<span class="text-muted">—</span>@endif</td>
                        <td>{{ $a->created_at?->format('d-m-Y') }}@if($a->booking_date)<div class="small text-muted">Booked: {{ $a->booking_date }}</div>@endif</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.bulk-article.unmapped.edit', $a) }}" class="btn btn-sm btn-primary">Edit / Map with Order ID</a>
                            <form method="post" action="{{ route('admin.bulk-article.unmapped.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Delete this not mapped article?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center p-4">No pending Excel rows.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $articles->links() }}</div>
    </div>
</div>
@endsection
