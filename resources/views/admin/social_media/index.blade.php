@extends('layouts.admin')

@section('title', 'Social Media Master - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Social Media Master</h3>
        <p class="text-muted small mb-0">Manage WhatsApp launcher and official social media channel links</p>
    </div>
    <a href="{{ route('admin.social-media.create') }}" class="btn btn-warning rounded-pill fw-bold px-4">
        <i class="fa-solid fa-plus me-1"></i> Add Social Link / WhatsApp
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Platform Type</th>
                        <th>Details / Link</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($socialMedias as $soc)
                        <tr>
                            <td>
                                <span class="fs-5 me-2"><i class="fa-brands fa-{{ $soc->type === 'whatsapp' ? 'whatsapp text-success' : $soc->type }}"></i></span>
                                <strong class="text-uppercase text-dark">{{ $soc->type }}</strong>
                            </td>
                            <td>
                                @if($soc->type === 'whatsapp')
                                    <span class="fw-bold text-success">{{ $soc->country_code }} {{ $soc->phone_number }}</span>
                                @else
                                    <a href="{{ $soc->url }}" target="_blank" class="text-decoration-none small">{{ $soc->url }}</a>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $soc->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($soc->status) }}
                                </span>
                            </td>
                            <td>{{ $soc->sort_order }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.social-media.edit', $soc->id) }}" class="btn btn-sm btn-outline-dark me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

                                <form action="{{ route('admin.social-media.destroy', $soc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this social media entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No social media links added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
