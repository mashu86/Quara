@extends('layouts.admin')

@section('title', 'Notifications - QUARA WALDROP Admin')

@section('content')
<style>
    .notif-icon {
        font-size: 1.1rem;
        margin-right: 6px;
    }
    @media (max-width: 576px) {
        .notif-header-title {
            font-size: 1.15rem !important;
        }
        .notif-header-subtitle {
            font-size: 0.72rem !important;
        }
        .mark-all-btn {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.6rem !important;
            border-radius: 8px !important;
        }
        .notif-item {
            padding: 0.75rem 0.85rem !important;
        }
        .notif-icon {
            font-size: 0.88rem !important;
            margin-right: 5px !important;
        }
        .notif-title {
            font-size: 0.85rem !important;
            margin-bottom: 0.2rem !important;
        }
        .notif-msg {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .notif-time {
            font-size: 0.68rem !important;
        }
        .notif-action-btn {
            font-size: 0.74rem !important;
            padding: 0.25rem 0.6rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 notif-header-title">Notifications</h3>
        <p class="text-muted small mb-0 notif-header-subtitle">Real-time alerts for new orders and stock events</p>
    </div>
    @if($notifications->where('is_read', false)->count() > 0)
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-dark rounded-3 rounded-md-pill btn-sm px-2.5 px-md-3 py-1 py-md-1.5 mark-all-btn fw-bold shadow-sm" title="Mark All as Read">
                <i class="fa-solid fa-check-double me-1"></i><span class="d-none d-sm-inline"> Mark All as Read</span><span class="d-inline d-sm-none"> Mark All</span>
            </button>
        </form>
    @endif
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notif)
                <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 p-3 p-md-4 notif-item {{ !$notif->is_read ? 'bg-light fw-semibold' : 'bg-white text-muted' }}">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark notif-title d-flex align-items-center">
                            <span class="text-{{ in_array($notif->type, ['new_order', 'order']) ? 'warning' : 'danger' }} me-2 notif-icon">
                                <i class="fa-solid fa-{{ in_array($notif->type, ['new_order', 'order']) ? 'bag-shopping' : 'bell' }}"></i>
                            </span>
                            <span>{{ $notif->title }}</span>
                        </h6>
                        <p class="mb-1 text-secondary small notif-msg">{{ $notif->message }}</p>
                        <span class="extra-small text-muted d-block notif-time" style="font-size: 0.72rem;">{{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('M d, h:i A') }})</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-1 mt-sm-0 align-self-end align-self-sm-center">
                        @if($notif->order_id)
                            <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm font-semibold notif-action-btn" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> <span class="d-none d-xs-inline">View Order</span><span class="d-inline d-xs-none">View</span>
                                </button>
                            </form>
                        @elseif(!$notif->is_read)
                            <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm rounded-pill border notif-action-btn" style="font-size: 0.78rem;">Mark Read</button>
                            </form>
                        @else
                            <span class="badge bg-secondary rounded-pill px-2 py-1" style="font-size: 0.7rem;">Read</span>
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
