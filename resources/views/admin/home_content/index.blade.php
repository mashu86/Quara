@extends('layouts.admin')

@section('title', 'Home Main Content Master - QUARA WALDROP Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .hc-header-title {
            font-size: 1.15rem !important;
        }
        .hc-header-subtitle {
            font-size: 0.72rem !important;
        }
        .hc-top-btn {
            font-size: 0.8rem !important;
            padding: 0.45rem 0.85rem !important;
        }
        .table th, .table td {
            font-size: 0.76rem !important;
            padding: 0.5rem 0.65rem !important;
        }
        .table .btn-sm {
            font-size: 0.72rem !important;
            padding: 0.2rem 0.45rem !important;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 hc-header-title">Home Main Content Master</h3>
        <p class="text-muted small mb-0 hc-header-subtitle">Manage rich text, promo banners, and custom styling for the homepage</p>
    </div>
    <a href="{{ route('admin.home-content.create') }}" class="btn btn-warning rounded-pill fw-bold px-3 py-2 hc-top-btn shadow-sm text-dark w-100 w-sm-auto text-center" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add Home Content Block">
        <i class="fa-solid fa-plus me-1"></i> Add Home Content Block
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image Preview</th>
                        <th>Title</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeContents as $hc)
                        <tr>
                            <td>
                                @if($hc->image_url)
                                    <img src="{{ $hc->image_url }}" alt="{{ $hc->title }}" class="rounded-3 border" style="width: 70px; height: 45px; object-fit: cover;">
                                @else
                                    <span class="text-muted small">No Image</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">{{ $hc->title }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $hc->image_position }}</span></td>
                            <td>
                                <span class="badge bg-{{ $hc->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($hc->status) }}
                                </span>
                            </td>
                            <td>{{ $hc->created_at->format('M d, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.home-content.edit', $hc->id) }}" class="btn btn-sm btn-outline-dark me-1" title="Edit Entry"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

                                <form action="{{ route('admin.home-content.destroy', $hc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this home content entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Entry"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No home content blocks created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
