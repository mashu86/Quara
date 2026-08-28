@extends('layouts.admin')

@section('title', 'Delivery Price Master - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="font-size: 0.95rem;">Delivery Price Master</h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Create flexible delivery charge rules based on Cart Item Count or Price.</p>
    </div>
    <a href="{{ route('admin.shipping-policies.create') }}" class="btn btn-warning rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New Policy">
        <i class="fa-solid fa-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Add Policy</span>
    </a>
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
                        <th class="ps-3">Priority</th>
                        <th>Policy Name</th>
                        <th>Criteria</th>
                        <th>Conditions</th>
                        <th>Delivery Type</th>
                        <th>Charge (₹)</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td class="ps-3"><span class="badge bg-secondary rounded-circle">{{ $policy->priority }}</span></td>
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
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1 flex-nowrap">
                                    <a href="{{ route('admin.shipping-policies.edit', $policy->id) }}" class="btn btn-sm btn-outline-dark rounded-3 px-2 py-1" style="font-size: 0.75rem;" title="Edit Policy">
                                        <i class="fa-solid fa-pen-to-square"></i><span class="d-none d-sm-inline ms-1">Edit</span>
                                    </a>

                                    <form action="{{ route('admin.shipping-policies.destroy', $policy->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete this policy?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" style="font-size: 0.75rem;" title="Delete Policy">
                                            <i class="fa-solid fa-trash"></i><span class="d-none d-sm-inline ms-1">Delete</span>
                                        </button>
                                    </form>
                                </div>
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
