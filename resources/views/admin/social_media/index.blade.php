@extends('layouts.admin')

@section('title', 'Social Media Master - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .soc-header-title {
            font-size: 1.15rem !important;
        }
        .soc-header-subtitle {
            font-size: 0.72rem !important;
        }
        .soc-top-btn {
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
        <h3 class="fw-bold mb-1 soc-header-title">Social Media Master</h3>
        <p class="text-muted small mb-0 soc-header-subtitle">Manage WhatsApp launcher and official social media channel links</p>
    </div>
    <a href="{{ route('admin.social-media.create') }}" class="btn btn-warning rounded-pill fw-bold px-3 py-2 soc-top-btn shadow-sm text-dark w-100 w-sm-auto text-center" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add Social Link / WhatsApp">
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
                        <th>Sort</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($socialMedias as $soc)
                        <tr>
                            <td>
                                <span class="fs-5 me-1.5"><i class="fa-brands fa-{{ $soc->type === 'whatsapp' ? 'whatsapp text-success' : $soc->type }}"></i></span>
                                <strong class="text-uppercase text-dark">{{ $soc->type }}</strong>
                            </td>
                            <td>
                                @if($soc->type === 'whatsapp')
                                    <span class="fw-bold text-success">{{ $soc->country_code }} {{ $soc->phone_number }}</span>
                                @else
                                    <a href="{{ $soc->url }}" target="_blank" class="text-decoration-none small text-truncate d-inline-block" style="max-width: 180px;">{{ $soc->url }}</a>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $soc->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($soc->status) }}
                                </span>
                            </td>
                            <td>{{ $soc->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.social-media.edit', $soc->id) }}" class="btn btn-sm btn-outline-dark me-1" title="Edit Entry"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

                                <form action="{{ route('admin.social-media.destroy', $soc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this social media entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Entry"><i class="fa-solid fa-trash"></i> Delete</button>
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
