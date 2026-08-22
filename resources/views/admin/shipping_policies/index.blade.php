@extends('layouts.admin')

@section('title', 'Delivery Price Master - QUARA WALDROP Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">Delivery Price Master (Shipping Policies)</h3>
        <p class="text-muted small mb-0">Create flexible delivery charge rules based on Cart Item Count or Cart Price Subtotal.</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('admin.shipping-policies.create') }}" class="btn btn-warning rounded-pill px-4 py-2 fw-bold shadow-sm text-dark me-2">
            <i class="fa-solid fa-plus me-1"></i> Add New Policy
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Priority</th>
                        <th>Policy Name</th>
                        <th>Criteria</th>
                        <th>Conditions</th>
                        <th>Delivery Type</th>
                        <th>Charge (₹)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td class="ps-4"><span class="badge bg-secondary rounded-circle">{{ $policy->priority }}</span></td>
                            <td class="fw-bold text-dark">{{ $policy->name }}</td>
                            <td>
                                @if($policy->criteria_type === 'cart_count')
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-layer-group me-1"></i> Cart Count</span>
                                @else
                                    <span class="badge bg-primary"><i class="fa-solid fa-indian-rupee-sign me-1"></i> Cart Price</span>
                                @endif
                            </td>
                            <td>
                                <code>
                                    From: {{ $policy->from_operator }} {{ $policy->criteria_type === 'cart_price' ? '₹' : '' }}{{ number_format($policy->from_value, 2) }}
                                    @if($policy->to_value !== null)
                                        | To: {{ $policy->to_operator }} {{ $policy->criteria_type === 'cart_price' ? '₹' : '' }}{{ number_format($policy->to_value, 2) }}
                                    @endif
                                </code>
                            </td>
                            <td>
                                @if($policy->delivery_type === 'free')
                                    <span class="badge bg-success"><i class="fa-solid fa-truck-fast me-1"></i> Free Delivery</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-coins me-1"></i> Custom Charge</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                {{ $policy->delivery_type === 'free' ? '₹0.00' : '₹' . number_format($policy->charge_amount, 2) }}
                            </td>
                            <td>
                                @if($policy->status === 'active')
                                    <span class="badge bg-success rounded-pill px-3">Active</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.shipping-policies.edit', $policy->id) }}" class="btn btn-sm btn-outline-dark rounded-pill me-1">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <form action="{{ route('admin.shipping-policies.destroy', $policy->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this policy?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-truck-ramp-box fs-1 mb-3 d-block text-secondary"></i>
                                No shipping policies created yet. Default standard delivery will apply until a policy is added.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
