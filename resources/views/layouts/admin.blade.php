<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - QUARA WALDROP')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon / Shop Icon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

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
            .admin-sidebar { margin-left: -260px; }
            .admin-sidebar.show { margin-left: 0; }
            .admin-main { margin-left: 0; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand-header text-center">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="QUARA WALDROP" style="max-height: 42px;">
            </a>
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
                    <a href="{{ route('admin.shipping-policies.index') }}" class="nav-link {{ request()->routeIs('admin.shipping-policies.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-ramp-box me-2"></i> Delivery Price Master
                    </a>
                </li>

                <li class="nav-heading">Orders & Offline Sales</li>
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-receipt me-2"></i> Website Orders
                        @php $pendingCount = \App\Models\Order::where('order_status', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                            <span class="badge bg-warning text-dark ms-auto fw-bold">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.manual-sales.index') }}" class="nav-link {{ request()->routeIs('admin.manual-sales.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-store me-2"></i> Manual Offline Sales
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell me-2"></i> Notifications
                        @php $unreadCount = \App\Models\Notification::where('is_read', false)->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="badge-notification ms-auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-heading">Accounts & Expenses</li>
                <li class="nav-item">
                    <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-wallet me-2"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.profit-loss') }}" class="nav-link {{ request()->routeIs('admin.reports.profit-loss') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie me-2"></i> Profit & Loss Report
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

            <h5 class="mb-0 font-bold d-none d-sm-block">QUARA WALDROP <span class="badge bg-dark ms-2 fw-normal">Admin Panel</span></h5>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.notifications.index') }}" class="position-relative text-dark fs-5">
                    <i class="fa-solid fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-shield me-1 text-warning"></i>
                        <span>{{ Auth::user()->name ?? 'Quara Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('adminSidebar')?.classList.toggle('show');
        });
    </script>
    @yield('scripts')
</body>
</html>
