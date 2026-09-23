<div class="modal fade" id="geminiApiKeyPickerModal" tabindex="-1" aria-labelledby="geminiApiKeyPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold" id="geminiApiKeyPickerModalLabel">
                        <i class="fa-solid fa-eye text-warning me-2"></i>Google Gemini API Keys
                    </h5>
                    <p class="text-muted small mb-0">View saved keys and choose which key AI tools should use.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                @forelse($geminiApiKeys as $geminiApiKey)
                    <div class="d-flex align-items-center justify-content-between gap-3 border rounded-3 p-3 mb-2 {{ $geminiApiKey->is_active ? 'border-success bg-success bg-opacity-10' : 'bg-light' }}">
                        <div class="min-w-0">
                            <div class="fw-semibold text-dark text-break">
                                {{ $geminiApiKey->name }}
                                @if($geminiApiKey->is_active)
                                    <span class="badge bg-success ms-1">Active</span>
                                @endif
                            </div>
                            <code class="small text-muted">{{ $geminiApiKey->masked_key }}</code>
                        </div>
                        @if(!$geminiApiKey->is_active)
                            <form action="{{ route('admin.gemini-keys.activate', $geminiApiKey) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">Use this key</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fa-solid fa-key fs-2 text-muted mb-2"></i>
                        <p class="text-muted mb-3">No Google API keys have been saved yet.</p>
                        <a href="{{ route('admin.gemini-keys.index') }}" class="btn btn-warning btn-sm rounded-pill px-3">Add an API key</a>
                    </div>
                @endforelse
            </div>
            @if($geminiApiKeys->isNotEmpty())
                <div class="modal-footer border-top justify-content-between">
                    <a href="{{ route('admin.gemini-keys.index') }}" class="small text-decoration-none">Manage keys</a>
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Done</button>
                </div>
            @endif
        </div>
    </div>
</div>
