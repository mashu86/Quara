@extends('layouts.admin')

@section('title', 'Available Stock - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-boxes-stacked text-success me-2"></i>Available Stock</h4>
        <p class="text-muted mb-0">Active products on the website that are not sold out, after reserved and booked pieces are set aside.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Available Products</div>
            <div class="fs-3 fw-bold text-success">{{ number_format($totalProducts) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Available Pieces</div>
            <div class="fs-3 fw-bold">{{ number_format($totalUnits) }}</div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th class="text-end">Products</th>
                    <th class="text-end">Pieces</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $row['name'] }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($row['products']) }}</td>
                        <td class="text-end">{{ number_format($row['units']) }}</td>
                        <td class="text-end">
                            @if($row['id'])
                                <a href="{{ route('admin.products.index', ['category_id' => $row['id'], 'stock_status' => 'in_stock', 'status' => 'active']) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No available products.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">A product listed in more than one category is counted in each of them.</p>
@endsection
