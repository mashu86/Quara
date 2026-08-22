@extends('layouts.admin')

@section('title', 'Home Main Content Master - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Home Main Content Master</h3>
        <p class="text-muted small mb-0">Manage rich text, promo banners, and custom styling for the homepage</p>
    </div>
    <a href="{{ route('admin.home-content.create') }}" class="btn btn-warning rounded-pill fw-bold px-4">
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
                        <th>Image Position</th>
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
                                    <img src="{{ $hc->image_url }}" alt="{{ $hc->title }}" class="rounded-3 border" style="width: 80px; height: 50px; object-fit: cover;">
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
                            <td class="text-end">
                                <a href="{{ route('admin.home-content.edit', $hc->id) }}" class="btn btn-sm btn-outline-dark me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

                                <form action="{{ route('admin.home-content.destroy', $hc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this home content entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
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
