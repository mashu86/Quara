@extends('layouts.admin')

@section('title', 'Google Gemini API Keys Master - Admin')

@section('styles')
<style>
    .key-card { border: 0; border-radius: 1rem; box-shadow: 0 4px 18px rgba(20, 20, 25, .06); }
    .key-card .card-header { background: #fff; border-bottom: 1px solid #ececf1; padding: 1.1rem 1.25rem; }
    .active-badge { font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 50rem; font-weight: 700; }
    .radio-active-btn { cursor: pointer; width: 1.25rem; height: 1.25rem; accent-color: #198754; }
    .masked-code { font-family: monospace; background: #f8f9fa; padding: 0.35rem 0.65rem; border-radius: 0.4rem; border: 1px solid #e9ecef; }
    
    /* Mobile Card Styles */
    .gemini-key-mobile-card {
        border-radius: 0.85rem;
        background: #ffffff;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .gemini-key-mobile-card.is-active-card {
        border-left: 4px solid #198754;
        background: #f8fff9;
    }
    .radio-tap-label {
        cursor: pointer;
        user-select: none;
        padding: 0.45rem 0.75rem;
        border-radius: 0.5rem;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .gemini-key-mobile-card.is-active-card .radio-tap-label {
        background: #d1e7dd;
        color: #0f5132;
    }
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 settings-title"><i class="fa-solid fa-key text-warning me-2"></i> Google Gemini API Keys Master</h3>
        <p class="text-muted small mb-0">Manage multiple Google Gemini AI keys. Select <strong>ONE active key</strong> at a time.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button type="button" class="btn btn-warning rounded-pill px-3 py-2 fw-bold text-dark text-nowrap shadow-sm" data-bs-toggle="modal" data-bs-target="#addKeyModal" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New API Key">
            <i class="fa-solid fa-plus me-1"></i>
            <span class="d-none d-sm-inline">Add New API Key</span>
            <span class="d-inline d-sm-none">New Key</span>
        </button>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-bold text-nowrap" title="Master Settings">
            <i class="fa-solid fa-gears"></i>
            <span class="d-none d-sm-inline ms-1">Settings</span>
        </a>
    </div>
</div>

<!-- Info Alert Banner -->
<div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
    <div class="d-flex align-items-start gap-3">
        <div class="fs-4 text-info mt-1"><i class="fa-solid fa-circle-info"></i></div>
        <div>
            <h6 class="fw-bold mb-1">Single Active Key Rule & Google AI Studio API Key Guide</h6>
            <div class="small mb-2">
                Only the key marked as <span class="badge bg-success">ACTIVE</span> will be used by the system. If your active key hits its daily limit (20 requests/day), the system will automatically fall back to your next backup key, or you can switch keys manually below.
            </div>
            <div class="small text-dark fw-bold bg-white p-2 rounded border">
                <i class="fa-solid fa-key text-warning me-1"></i> How to get a valid Google Gemini API Key:
                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-primary text-decoration-underline ms-1">https://aistudio.google.com/app/apikey</a>
                <br>
                <span class="text-muted font-monospace" style="font-size: 0.8rem;">Note: Real Google Gemini API Keys start with <code>AIzaSy...</code> (39 characters).</span>
            </div>
        </div>
    </div>
</div>

<!-- Keys Overview Card -->
<div class="card key-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i> Saved Gemini API Keys List</h5>
        <span class="badge bg-dark rounded-pill px-3 py-1.5">Total Keys: {{ $keys->count() }}</span>
    </div>
    <div class="card-body p-3 p-md-0">
        @if($keys->isEmpty())
            <div class="text-center py-5">
                <i class="fa-solid fa-key fs-1 text-muted opacity-50 mb-3"></i>
                <h6 class="fw-bold text-secondary">No Gemini API Keys Saved</h6>
                <p class="text-muted small">Click "Add New API Key" above to add your first Google AI Studio key.</p>
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                    <i class="fa-solid fa-plus me-1"></i> Add API Key Now
                </button>
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-center" style="width: 90px;">ACTIVE</th>
                            <th>Key Name</th>
                            <th>API Key (Masked)</th>
                            <th>Status</th>
                            <th>Added Date</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keys as $key)
                            <tr class="{{ $key->is_active ? 'table-success' : '' }}">
                                <td class="ps-4 text-center">
                                    <form action="{{ route('admin.gemini-keys.activate', $key) }}" method="POST" id="activate-form-dt-{{ $key->id }}">
                                        @csrf
                                        <input type="radio" name="active_key_radio_dt" class="radio-active-btn" 
                                               title="Click to set this key as Active"
                                               @checked($key->is_active) 
                                               onchange="document.getElementById('activate-form-dt-{{ $key->id }}').submit();">
                                    </form>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $key->name }}</span>
                                    @if($key->is_active)
                                        <span class="ms-2 badge bg-success active-badge"><i class="fa-solid fa-check me-1"></i> ACTIVE</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="masked-code">{{ $key->masked_key }}</code>
                                </td>
                                <td>
                                    @if($key->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">Active in System</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill">Inactive Backup</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $key->created_at ? $key->created_at->format('M d, Y h:i A') : '—' }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#editKeyModal-{{ $key->id }}" title="Edit Key">
                                            <i class="fa-solid fa-pen me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.gemini-keys.destroy', $key) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this API Key?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Delete Key">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards View -->
            <div class="d-block d-md-none">
                @foreach($keys as $key)
                    <div class="gemini-key-mobile-card {{ $key->is_active ? 'is-active-card' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $key->name }}</h6>
                                <div class="text-muted extra-small" style="font-size: 0.72rem;">Added: {{ $key->created_at ? $key->created_at->format('M d, Y h:i A') : '—' }}</div>
                            </div>
                            @if($key->is_active)
                                <span class="badge bg-success rounded-pill px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> ACTIVE</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-2.5 py-1">INACTIVE</span>
                            @endif
                        </div>

                        <!-- Active Radio Tap Bar -->
                        <form action="{{ route('admin.gemini-keys.activate', $key) }}" method="POST" id="activate-form-mb-{{ $key->id }}" class="mb-3">
                            @csrf
                            <label class="radio-tap-label w-100">
                                <input type="radio" name="active_key_radio_mb" class="radio-active-btn" 
                                       @checked($key->is_active) 
                                       onchange="document.getElementById('activate-form-mb-{{ $key->id }}').submit();">
                                <span>{{ $key->is_active ? 'Currently Active Key' : 'Tap to Set as Active Key' }}</span>
                            </label>
                        </form>

                        <div class="mb-3">
                            <label class="form-label extra-small text-muted mb-1 fw-bold">API KEY (MASKED)</label>
                            <div><code class="masked-code d-inline-block w-100 text-truncate">{{ $key->masked_key }}</code></div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editKeyModal-{{ $key->id }}">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                            <form action="{{ route('admin.gemini-keys.destroy', $key) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this API Key?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Edit Modals (Shared for Desktop & Mobile) -->
            @foreach($keys as $key)
                <div class="modal fade text-start" id="editKeyModal-{{ $key->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form action="{{ route('admin.gemini-keys.update', $key) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen text-primary me-2"></i> Edit API Key</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Key Name / Description</label>
                                        <input type="text" name="name" class="form-control rounded-3" value="{{ $key->name }}" required maxlength="100">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Google Gemini API Key</label>
                                        <input type="password" name="api_key" class="form-control rounded-3" placeholder="Leave blank to keep current key">
                                        <div class="form-text">Current: <code>{{ $key->masked_key }}</code>. Only enter a value if you want to replace it.</div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Key</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Add Key Modal -->
<div class="modal fade" id="addKeyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.gemini-keys.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-warning me-2"></i> Add New Gemini API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Key Name / Description <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Gemini Key 1 - Main, Key 2 - Backup" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Google Gemini API Key <span class="text-danger">*</span></label>
                        <input type="text" name="api_key" class="form-control rounded-3" placeholder="AQ... or AIzaSy..." required>
                        <div class="form-text">Paste the key generated from Google AI Studio.</div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="set_active" value="1" id="setActiveCheckbox" checked>
                        <label class="form-check-label fw-bold" for="setActiveCheckbox">Set as Active Key immediately</label>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">Save API Key</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
