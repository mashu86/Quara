@extends('layouts.admin')

@section('title', 'Encrypt / Decrypt - Admin')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1"><i class="fa-solid fa-lock text-warning me-2"></i> Encrypt / Decrypt</h3>
    <p class="text-muted small mb-0">Enter a value to encrypt it, or paste an encrypted value to decrypt it.</p>
</div>

@if($error)
    <div class="alert alert-danger rounded-3">{{ $error }}</div>
@endif

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4">
        <form method="POST">
            @csrf
            <label for="text" class="form-label fw-semibold">Text</label>
            <textarea id="text" name="text" class="form-control rounded-3 font-monospace" rows="6" maxlength="10000" required>{{ $input }}</textarea>
            @error('text')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" formaction="{{ route('admin.credential-details.encrypt') }}" class="btn btn-warning rounded-pill px-4 fw-semibold">Encrypt</button>
                <button type="submit" formaction="{{ route('admin.credential-details.decrypt') }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">Decrypt</button>
            </div>
        </form>

        @if($mode)
            <div class="mt-4">
                <label for="result" class="form-label fw-semibold">{{ $mode }} result</label>
                <textarea id="result" class="form-control rounded-3 font-monospace" rows="6" readonly>{{ $result }}</textarea>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill mt-2" onclick="navigator.clipboard.writeText(document.getElementById('result').value)">Copy result</button>
            </div>
        @endif
    </div>
</div>
@endsection
