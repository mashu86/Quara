<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - ' . $siteName)</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon / Shop Icon -->
    <link rel="icon" href="{{ $siteFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $siteFaviconUrl }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --qw-gold: #D4AF37;
            --qw-gold-dark: #996515;
            --qw-black: #0D0D0E;
            --qw-sidebar-bg: #141416;
            --qw-light-bg: #F5F5F8;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--qw-light-bg);
            color: #222225;
            letter-spacing: 0.2px;
        }

        h1, h2, h3, h4, h5, .brand-header {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background-color: var(--qw-sidebar-bg);
            height: 100vh;
            display: flex;
            flex-direction: column;
            color: #CCCCCC;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }

        .admin-sidebar .brand-header {
            padding: 22px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            background-color: #0D0D0E;
            flex-shrink: 0;
        }

        .admin-sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 20px;
        }

        /* Custom Subtle Scrollbar */
        .admin-sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .admin-sidebar-nav::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }
        .admin-sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--qw-gold);
            border-radius: 4px;
        }

        .admin-sidebar .nav-link {
            color: #AAAAAA;
            padding: 12px 20px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }

        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            color: #FFFFFF;
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.18) 0%, rgba(212, 175, 55, 0.02) 100%);
            border-left-color: var(--qw-gold);
        }

        .admin-sidebar .nav-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--qw-gold);
            padding: 18px 20px 6px;
            font-weight: 700;
        }

        /* Main Content wrapper */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .admin-topbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E6E6EC;
            padding: 16px 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .badge-notification {
            background-color: #DC3545;
            color: #FFF;
            font-size: 0.7rem;
            border-radius: 50px;
            padding: 3px 7px;
        }

        /* Card Styling */
        .stat-card {
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 22px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        @media (max-width: 991px) {
            .admin-sidebar { margin-left: -260px; z-index: 1040; }
            .admin-sidebar.show { margin-left: 0; }
            .admin-main { margin-left: 0; }
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Global Mobile Table Responsive Styles for All Admin Pages */
        @media (max-width: 767.98px) {
            .table th {
                font-size: 0.76rem !important;
                padding: 0.45rem 0.5rem !important;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                white-space: nowrap;
            }
            .table td {
                font-size: 0.78rem !important;
                padding: 0.45rem 0.5rem !important;
            }
            .table h6, .table .h6, .table h5, .table .h5 {
                font-size: 0.82rem !important;
                margin-bottom: 0 !important;
            }
            .table code {
                font-size: 0.75rem !important;
            }
            .table .badge {
                font-size: 0.70rem !important;
                padding: 0.25rem 0.45rem !important;
            }
            .table .btn-sm {
                font-size: 0.74rem !important;
                padding: 0.2rem 0.45rem !important;
            }
            .table .small, .table small {
                font-size: 0.72rem !important;
            }
        }

        /* Admin Pagination Mobile Alignment & Styling */
        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }
        .page-item .page-link {
            color: var(--qw-black);
            border-radius: 8px !important;
            padding: 0.35rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 500;
            border: 1px solid #E2E8F0;
        }
        .page-item.active .page-link {
            background-color: var(--qw-gold) !important;
            border-color: var(--qw-gold) !important;
            color: #000000 !important;
            font-weight: 700;
        }
        .page-item.disabled .page-link {
            color: #A0AEC0;
            background-color: #F7FAFC;
        }
        @media (max-width: 575.98px) {
            .pagination {
                justify-content: center;
                font-size: 0.76rem;
            }
            .page-item .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand-header d-flex justify-content-between align-items-center px-3 px-lg-4">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" style="max-height: 42px; max-width: 190px; object-fit: contain;">
            </a>
            <button type="button" class="btn text-white p-1 d-lg-none border-0 shadow-none fs-5" id="sidebarCloseBtn" title="Close Menu">
                <i class="fa-solid fa-xmark text-warning"></i>
            </button>
        </div>

        <nav class="mt-3 admin-sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line me-2 text-gold"></i> Dashboard
                    </a>
                </li>

                <li class="nav-heading">Catalog Management</li>
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group me-2"></i> Category Master
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shirt me-2"></i> Product Master
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.display-order.index') }}" class="nav-link {{ request()->routeIs('admin.display-order.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrows-up-down-left-right me-2 text-warning"></i> Display & Sorting
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.shipping-policies.index') }}" class="nav-link {{ request()->routeIs('admin.shipping-policies.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-ramp-box me-2"></i> Delivery Price Master
                    </a>
                </li>

                <li class="nav-heading">Orders & Offline Sales</li>
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-receipt me-2"></i> My Sales
                        @if(($unreadOrderCount ?? 0) > 0)
                            <span class="badge bg-danger text-white ms-auto fw-bold">{{ $unreadOrderCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.manual-sales.index') }}" class="nav-link {{ request()->routeIs('admin.manual-sales.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-store me-2"></i> Offline Sales
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.order-operations.index') }}" class="nav-link {{ request()->routeIs('admin.order-operations.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-rotate-left me-2 text-warning"></i> Order Operations
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell me-2"></i> Notifications
                        @if(($unreadCount ?? 0) > 0)
                            <span class="badge-notification ms-auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-heading">Accounts & Financials</li>
                <li class="nav-item">
                    <a href="{{ route('admin.incomes.index') }}" class="nav-link {{ request()->routeIs('admin.incomes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-hand-holding-dollar me-2 text-success"></i> Additional Incomes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-wallet me-2"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.contractual-posts.index') }}" class="nav-link {{ request()->routeIs('admin.contractual-posts.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-envelopes-bulk me-2 text-warning"></i> Contractual Post
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.profit-loss') }}" class="nav-link {{ request()->routeIs('admin.reports.profit-loss') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie me-2"></i> Profit & Loss Report
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.razorpay-charges') }}" class="nav-link {{ request()->routeIs('admin.reports.razorpay-charges') ? 'active' : '' }}">
                        <i class="fa-brands fa-credit-card me-2"></i> Razorpay Charges Report
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear me-2"></i> Master Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.payment-check.index') }}" class="nav-link {{ request()->routeIs('admin.payment-check.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-credit-card me-2"></i> Razorpay Payment Check
                    </a>
                </li>

                <li class="nav-heading">Site Content</li>
                <li class="nav-item">
                    <a href="{{ route('admin.home-content.index') }}" class="nav-link {{ request()->routeIs('admin.home-content.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders me-2"></i> Home Main Content
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.social-media.index') }}" class="nav-link {{ request()->routeIs('admin.social-media.*') ? 'active' : '' }}">
                        <i class="fa-brands fa-whatsapp me-2"></i> Social Media Master
                    </a>
                </li>

                <li class="nav-heading">Account</li>
                <li class="nav-item">
                    <a href="{{ route('home') }}" target="_blank" class="nav-link">
                        <i class="fa-solid fa-globe me-2"></i> View Customer Site
                    </a>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link border-0 bg-transparent text-start w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fa-solid fa-key me-2 text-warning"></i> Change Password
                    </button>
                </li>
                <li class="nav-item">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent text-danger w-100 text-start">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Admin Main Wrapper -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar d-flex justify-content-between align-items-center">
            <button class="btn btn-light d-lg-none" type="button" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h5 class="mb-0 font-bold d-none d-sm-block">{{ $siteName }} <span class="badge bg-dark ms-2 fw-normal">Admin Panel</span></h5>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="position-relative text-dark fs-5 p-2 d-inline-block text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: 330px; max-width: 90vw;">
                        <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between rounded-top-4">
                            <span class="fw-bold text-dark small"><i class="fa-solid fa-bell me-1 text-warning"></i> Notifications</span>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;">{{ $unreadCount }} New</span>
                            @endif
                        </div>
                        <div class="list-group list-group-flush overflow-auto" style="max-height: 280px;">
                            @forelse($recentNotifications as $notif)
                                <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="list-group-item list-group-item-action p-3 text-start border-0 border-bottom {{ !$notif->is_read ? 'bg-light text-dark fw-bold' : 'text-muted' }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="fs-6 text-warning mt-0.5">
                                                <i class="fa-solid {{ $notif->type === 'new_order' ? 'fa-bag-shopping' : 'fa-bell' }}"></i>
                                            </span>
                                            <div class="w-100">
                                                <div class="fw-bold text-dark small mb-0 d-flex align-items-center justify-content-between">
                                                    <span>{{ $notif->title }}</span>
                                                    @if(!$notif->is_read)
                                                        <span class="badge bg-primary rounded-circle p-1" style="width: 6px; height: 6px;" title="Unread"></span>
                                                    @endif
                                                </div>
                                                <div class="text-secondary small text-truncate" style="max-width: 240px; font-size: 0.78rem;">{{ $notif->message }}</div>
                                                <div class="text-muted extra-small mt-1" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </button>
                                </form>
                            @empty
                                <div class="p-3 text-center text-muted small">No recent notifications</div>
                            @endforelse
                        </div>
                        <div class="p-2 text-center bg-light border-top rounded-bottom-4">
                            <a href="{{ route('admin.notifications.index') }}" class="small fw-bold text-decoration-none text-dark">View All Notifications <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-shield me-1 text-warning"></i>
                        <span>{{ Auth::user()->name ?? 'Quara Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fa-solid fa-key me-2 text-warning"></i> Change Password
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                    <h5 class="modal-title font-serif fw-bold" id="changePasswordModalLabel">
                        <i class="fa-solid fa-key text-warning me-2"></i> Change Admin Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.change-password') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="old_password" class="form-label fw-semibold text-dark">Old Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 @error('old_password') is-invalid @enderror" id="old_password" name="old_password" placeholder="Enter old password" required>
                                <span class="input-group-text bg-light border-start-0 role-button toggle-password-btn" data-target="old_password" style="cursor: pointer;">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </span>
                            </div>
                            @error('old_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold text-dark">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 @error('new_password') is-invalid @enderror" id="new_password" name="new_password" placeholder="Enter new password (min 6 chars)" required minlength="6">
                                <span class="input-group-text bg-light border-start-0 role-button toggle-password-btn" data-target="new_password" style="cursor: pointer;">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </span>
                            </div>
                            @error('new_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label fw-semibold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-circle-check text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirm new password" required>
                                <span class="input-group-text bg-light border-start-0 role-button toggle-password-btn" data-target="new_password_confirmation" style="cursor: pointer;">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                            <i class="fa-solid fa-shield-halved me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openAdminSidebar() {
            adminSidebar?.classList.add('show');
            sidebarOverlay?.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeAdminSidebar() {
            adminSidebar?.classList.remove('show');
            sidebarOverlay?.classList.remove('show');
            document.body.style.overflow = '';
        }

        sidebarToggle?.addEventListener('click', function(e) {
            e.stopPropagation();
            if (adminSidebar?.classList.contains('show')) {
                closeAdminSidebar();
            } else {
                openAdminSidebar();
            }
        });

        sidebarCloseBtn?.addEventListener('click', closeAdminSidebar);
        sidebarOverlay?.addEventListener('click', closeAdminSidebar);

        function handleAdminFormSubmit(form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> UPLOADING & SAVING... PLEASE WAIT...';
                setTimeout(function() {
                    submitBtn.disabled = true;
                }, 10);
            }
            return true;
        }

        document.querySelectorAll('.toggle-password-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');
                if (input && icon) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });
    </script>
    @if($errors->has('old_password') || $errors->has('new_password'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalEl = document.getElementById('changePasswordModal');
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    </script>
    @endif
    @yield('scripts')
</body>
</html>
