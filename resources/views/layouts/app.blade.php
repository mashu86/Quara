<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QUARA WALDROP - Affordable & Trendy Ladies Western Wear')</title>
    <meta name="description" content="@yield('meta_description', 'Shop elegant, trendy & affordable ladies fashion, Korean tops, western dresses & daily chic wear at QUARA WALDROP.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon / Shop Icon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">


    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Classic Luxury Brand CSS -->
    <style>
        :root {
            --qw-gold: #D4AF37;
            --qw-gold-light: #F3E5AB;
            --qw-gold-dark: #996515;
            --qw-black: #111111;
            --qw-dark-gray: #1E1E1E;
            --qw-light-bg: #F9F9FB;
            --qw-border: #E8E8ED;
            --qw-white: #FFFFFF;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--qw-light-bg);
            color: var(--qw-black);
            overflow-x: hidden;
            letter-spacing: 0.2px;
        }

        h1, h2, h3, h4, .font-serif, .navbar-brand {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Gold accents */
        .text-gold { color: var(--qw-gold) !important; }
        .bg-gold { background-color: var(--qw-gold) !important; color: #fff; }
        .border-gold { border-color: var(--qw-gold) !important; }

        .btn-qw-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);
            color: #FFFFFF !important;
            border: none;
            font-weight: 600;
            letter-spacing: 0.8px;
            padding: 12px 28px;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 18px rgba(212, 175, 55, 0.3);
        }
        .btn-qw-gold:hover {
            background: linear-gradient(135deg, #AA7C11 0%, #D4AF37 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(212, 175, 55, 0.45);
        }

        .btn-qw-outline {
            border: 2px solid var(--qw-black);
            color: var(--qw-black);
            font-weight: 600;
            border-radius: 50px;
            padding: 10px 24px;
            transition: all 0.3s ease;
        }
        .btn-qw-outline:hover {
            background-color: var(--qw-black);
            color: var(--qw-white);
            transform: translateY(-2px);
        }

        /* Navbar Header */
        .qw-header {
            background-color: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 1040;
            transition: all 0.3s ease;
        }

        .qw-logo-img {
            max-height: 52px;
            width: auto;
            object-fit: contain;
        }

        .nav-link {
            font-weight: 500;
            color: var(--qw-black);
            padding: 10px 18px !important;
            letter-spacing: 0.6px;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--qw-gold) !important;
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -10px;
            background: linear-gradient(135deg, #D4AF37, #AA7C11);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
        }

        /* Classic Luxury Cards */
        .card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
        }

        /* Product Cards */
        .qw-product-card {
            background: var(--qw-white);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        .qw-product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 35px rgba(0, 0, 0, 0.08);
            border-color: rgba(212, 175, 55, 0.4);
        }
        .qw-product-img-wrapper {
            position: relative;
            width: 100%;
            padding-top: 125%; /* 4:5 aspect ratio */
            overflow: hidden;
            background-color: #F4F4F6;
        }
        .qw-product-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .qw-product-card:hover .qw-product-img {
            transform: scale(1.06);
        }
        .qw-discount-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #111111, #333333);
            color: #D4AF37;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 50px;
            border: 1px solid rgba(212, 175, 55, 0.4);
            z-index: 2;
        }

        .qw-out-of-stock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            transition: all 0.3s ease;
        }
        .qw-out-of-stock-badge {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 7px 16px;
            border-radius: 50px;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Category Card Grid */
        .qw-category-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 230px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            transition: transform 0.35s ease;
        }
        .qw-category-card:hover {
            transform: scale(1.02);
        }
        .qw-category-bg {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.68);
            transition: filter 0.4s ease;
        }
        .qw-category-card:hover .qw-category-bg {
            filter: brightness(0.58);
        }
        .qw-category-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }

        /* Floating WhatsApp Button */
        .qw-floating-whatsapp {
            position: fixed;
            bottom: 28px;
            right: 28px;
            width: 60px;
            height: 60px;
            background-color: #25D366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 32px;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.45);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .qw-floating-whatsapp:hover {
            transform: scale(1.12);
            color: #FFF;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.6);
        }

        /* Footer */
        .qw-footer {
            background-color: #0E0E10;
            color: #FFFFFF !important;
            border-top: 3px solid var(--qw-gold);
        }
        .qw-footer .text-muted {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .qw-footer a {
            color: #FFFFFF !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .qw-footer a:hover {
            color: var(--qw-gold) !important;
        }

        /* Responsive Mobile Utilities */
        @media (max-width: 576px) {
            .qw-logo-img { max-height: 44px; }
            .qw-category-card { height: 170px; }
            .qw-floating-whatsapp { width: 52px; height: 52px; font-size: 28px; bottom: 18px; right: 18px; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header Navigation -->
    <header class="qw-header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light py-2">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="QUARA WALDROP" class="qw-logo-img">
                </a>

                <div class="d-flex align-items-center d-lg-none ms-auto me-2">
                    <a href="#" class="btn btn-link text-dark p-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="fa-solid fa-magnifying-glass fs-5"></i>
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn btn-link text-dark p-2 position-relative">
                        <i class="fa-solid fa-bag-shopping fs-5"></i>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>

                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#qwNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="qwNavbar">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-uppercase fw-semibold">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('shop') ? 'active' : '' }}" href="{{ route('shop') }}">Shop</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Categories</a>
                            <ul class="dropdown-menu border-0 shadow-sm rounded-3">
                                @foreach($navCategories ?? [] as $navCat)
                                    <li><a class="dropdown-menu-item dropdown-item" href="{{ route('category.products', $navCat->slug) }}">{{ $navCat->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.my-orders') ? 'active' : '' }}" href="{{ route('customer.my-orders') }}">My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('order.tracking') ? 'active' : '' }}" href="{{ route('order.tracking') }}">Track Order</a>
                        </li>
                    </ul>

                    <div class="d-none d-lg-flex align-items-center gap-3">
                        <button type="button" class="btn btn-light rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fa-solid fa-magnifying-glass text-dark"></i>
                        </button>

                        <a href="{{ route('cart.index') }}" class="btn btn-outline-dark rounded-pill px-3 position-relative d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bag-shopping text-gold"></i>
                            <span>Cart</span>
                            @if($cartCount > 0)
                                <span class="badge bg-gold rounded-pill">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Alert Notifications -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="min-vh-100">
        @yield('content')
    </main>

    <!-- Floating WhatsApp Button -->
    @php
        $waLink = (isset($whatsappObj) && $whatsappObj) ? $whatsappObj->formatted_link : 'https://wa.me/918078037591';
    @endphp
    <a href="{{ $waLink }}" class="qw-floating-whatsapp" target="_blank" title="Chat with us on WhatsApp" aria-label="WhatsApp Support">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- Footer -->
    <footer class="qw-footer py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="QUARA WALDROP" class="mb-3" style="max-height: 50px;">
                    <p class="small text-muted mb-3">
                        QUARA WALDROP brings you affordable, high-fashion Korean tops, flattering western dresses, and trendy daily casual apparel. Dress beyond ordinary without breaking the bank.
                    </p>
                    <div class="d-flex gap-3">
                        @foreach($socialLinks ?? [] as $soc)
                            <a href="{{ $soc->formatted_link }}" target="_blank" class="fs-5"><i class="fa-brands fa-{{ $soc->type === 'whatsapp' ? 'whatsapp' : $soc->type }}"></i></a>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white text-uppercase font-serif mb-3 gold-gradient-text">Quick Links</h6>
                    <ul class="list-unstyled small d-grid gap-2">
                        <li><a href="{{ route('home') }}"><i class="fa-solid fa-angle-right me-1"></i> Home</a></li>
                        <li><a href="{{ route('shop') }}"><i class="fa-solid fa-angle-right me-1"></i> Shop All</a></li>
                        <li><a href="{{ route('order.tracking') }}"><i class="fa-solid fa-angle-right me-1"></i> Track Order</a></li>
                        <li><a href="{{ route('cart.index') }}"><i class="fa-solid fa-angle-right me-1"></i> View Cart</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white text-uppercase font-serif mb-3 gold-gradient-text">Categories</h6>
                    <ul class="list-unstyled small d-grid gap-2">
                        @foreach($navCategories->take(5) as $fCat)
                            <li><a href="{{ route('category.products', $fCat->slug) }}"><i class="fa-solid fa-angle-right me-1"></i> {{ $fCat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white text-uppercase font-serif mb-3 gold-gradient-text">Customer Support</h6>
                    <p class="small text-muted mb-2"><i class="fa-solid fa-envelope text-gold me-2"></i> support@quarawaldrop.com</p>
                    <p class="small text-muted mb-2"><i class="fa-brands fa-whatsapp text-gold me-2"></i> Instant WhatsApp Care</p>
                    <p class="small text-muted"><i class="fa-solid fa-truck text-gold me-2"></i> Fast pan-India delivery</p>
                </div>
            </div>

            <hr class="my-4 border-secondary opacity-25">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted">
                <p class="mb-0">&copy; {{ date('Y') }} <strong>QUARA WALDROP</strong>. All Rights Reserved.</p>
                <p class="mb-0">Designed with Elegance & Affordability</p>
            </div>
        </div>
    </footer>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-serif">Search QUARA WALDROP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('shop') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-lg border-end-0 rounded-start-pill" placeholder="Search tops, dresses, Korean style..." required>
                            <button type="submit" class="btn btn-qw-gold rounded-end-pill px-4">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
