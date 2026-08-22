@extends('layouts.admin')

@section('title', 'Notifications - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Notifications & System Alerts</h3>
        <p class="text-muted small mb-0">Real-time alerts for new orders and stock events</p>
    </div>
    @if($notifications->whereNull('read_at')->count() > 0)
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                <i class="fa-solid fa-check-double me-1"></i> Mark All as Read
            </button>
        </form>
    @endif
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notif)
                <li class="list-group-item d-flex justify-content-between align-items-center p-4 {{ $notif->read_at ? 'bg-light text-muted' : 'bg-white' }}">
                    <div class="d-flex align-items-start gap-3">
                        <span class="fs-4 text-{{ $notif->type === 'order' ? 'warning' : 'danger' }}">
                            <i class="fa-solid fa-{{ $notif->type === 'order' ? 'bag-shopping' : 'triangle-exclamation' }}"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">{{ $notif->title }}</h6>
                            <p class="mb-1 text-secondary">{{ $notif->message }}</p>
                            <span class="small text-muted">{{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('M d, h:i A') }})</span>
                        </div>
                    </div>

                    <div>
                        @if(!$notif->read_at)
                            <form action="{{ route('admin.notifications.mark-read', $notif->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm rounded-pill border">Mark Read</button>
                            </form>
                        @else
                            <span class="badge bg-secondary">Read</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center py-5 text-muted">No notifications in your inbox.</li>
            @endforelse
        </ul>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
