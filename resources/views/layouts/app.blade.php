<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary SEO Meta Tags -->
    <title>@yield('title', config('seo.default_title'))</title>
    <meta name="description" content="@yield('meta_description', config('seo.default_description'))">
    <meta name="keywords" content="@yield('meta_keywords', config('seo.default_keywords'))">
    <meta name="author" content="Quara Wardrobe">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Canonical URL (Dynamically Configurable) -->
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- Open Graph / Facebook Meta Tags -->
    <meta property="og:site_name" content="{{ $siteName ?? 'Quara Wardrobe' }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', View::getSection('title') ?? config('seo.default_title'))">
    <meta property="og:description" content="@yield('og_description', View::getSection('meta_description') ?? config('seo.default_description'))">
    <meta property="og:image" content="@yield('og_image', $siteLogoUrl)">
    <meta property="og:locale" content="{{ config('seo.locale', 'en_IN') }}">

    <!-- Twitter / X Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ config('seo.twitter_handle', '@quarawardrobe') }}">
    <meta name="twitter:title" content="@yield('og_title', View::getSection('title') ?? config('seo.default_title'))">
    <meta name="twitter:description" content="@yield('og_description', View::getSection('meta_description') ?? config('seo.default_description'))">
    <meta name="twitter:image" content="@yield('og_image', $siteLogoUrl)">

    <!-- Favicon / Shop Icon -->
    <link rel="icon" href="{{ $siteFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $siteFaviconUrl }}">

    <!-- Global Brand & Website JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "\u0040context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "{{ url('/') }}#organization",
          "name": "{{ $siteName ?? 'Quara Wardrobe' }}",
          "alternateName": ["Quara", "Quara Wardrobe", "Quara Waldrop", "Quara Online Shop", "Quara Store"],
          "url": "{{ url('/') }}",
          "logo": {
            "@type": "ImageObject",
            "url": "{{ $siteLogoUrl }}"
          },
          "contactPoint": {
            "@type": "ContactPoint",
            "email": "{{ $supportEmail }}",
            "contactType": "customer service"
          }
        },
        {
          "@type": "OnlineStore",
          "@id": "{{ url('/') }}#store",
          "name": "{{ $siteName ?? 'Quara Wardrobe' }}",
          "alternateName": ["Quara", "Quara Wardrobe", "Quara Waldrop", "Quara Online Store", "Quara Fashion Store"],
          "url": "{{ url('/') }}",
          "logo": "{{ $siteLogoUrl }}",
          "description": "Quara Wardrobe is an online fashion store offering elegant, trendy, and affordable ladies western wear, Korean tops, and stylish dresses with pan-India delivery.",
          "currenciesAccepted": "INR",
          "priceRange": "₹"
        },
        {
          "@type": "WebSite",
          "@id": "{{ url('/') }}#website",
          "url": "{{ url('/') }}",
          "name": "{{ $siteName ?? 'Quara Wardrobe' }}",
          "alternateName": ["Quara", "Quara Wardrobe", "Quara Waldrop"],
          "publisher": {
            "@id": "{{ url('/') }}#organization"
          },
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "{{ route('shop') }}?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        }
      ]
    }
    </script>
    @yield('json_ld')

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

        /* Global Mobile Headings Scaling */
        @media (max-width: 576px) {
            h1, .h1 { font-size: 1.35rem !important; }
            h2, .h2 { font-size: 1.22rem !important; }
            h3, .h3 { font-size: 1.12rem !important; }
            h4, .h4 { font-size: 1.0rem !important; }
            h5, .h5 { font-size: 0.90rem !important; }
            h6, .h6 { font-size: 0.82rem !important; }
            .display-1 { font-size: 2.1rem !important; }
            .display-2 { font-size: 1.9rem !important; }
            .display-3 { font-size: 1.7rem !important; }
            .display-4 { font-size: 1.5rem !important; }
            .display-5 { font-size: 1.3rem !important; }
            .display-6 { font-size: 1.15rem !important; }
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
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .btn-qw-outline:hover {
            background-color: var(--qw-black);
            color: var(--qw-white);
            transform: translateY(-2px);
        }

        .btn-qw-outline-gold {
            border: 1.5px solid #b8860b;
            color: #b8860b !important;
            font-weight: 700;
            border-radius: 50px;
            padding: 4px 14px;
            transition: all 0.25s ease;
            background: transparent;
        }
        .btn-qw-outline-gold:hover {
            background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);
            color: #FFFFFF !important;
            border-color: #AA7C11;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
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
            max-height: 88px;
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

        /* Product Cards & Prices */
        .qw-product-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.07) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
        }
        .qw-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.08), 0 0 1px rgba(212, 175, 55, 0.5) !important;
            border-color: rgba(212, 175, 55, 0.45) !important;
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
            transition: transform 0.5s ease;
        }
        .qw-product-card:hover .qw-product-img {
            transform: scale(1.05);
        }
        .qw-discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            color: #FFFFFF;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50rem;
            z-index: 2;
            letter-spacing: 0.4px;
            box-shadow: 0 4px 10px rgba(229, 57, 53, 0.3);
        }

        /* Product Card Price Styling & Red Cut Price */
        .qw-product-price {
            font-size: 1.15rem;
            font-weight: 800;
            color: #b8860b;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .qw-cut-price {
            font-size: 0.78rem !important;
            color: #dc3545 !important;
            text-decoration: line-through !important;
            text-decoration-color: #dc3545 !important;
            font-weight: 600;
            line-height: 1.2;
            opacity: 0.95;
            display: inline-block;
            margin-left: 5px !important;
        }
        .product-original-price {
            color: #dc3545 !important;
            text-decoration: line-through !important;
            text-decoration-color: #dc3545 !important;
        }

        /* Luxury Product Card Title & Elements */
        .qw-product-title {
            font-weight: 700;
            color: #111111;
            font-size: 0.84rem;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.35rem;
        }
        .qw-share-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: var(--qw-gold) !important;
            transition: all 0.2s ease;
        }
        .qw-share-btn:hover {
            background: var(--qw-gold);
            color: #111111 !important;
            transform: scale(1.1);
        }
        .qw-share-btn-floating:hover {
            background: #ffffff !important;
            transform: scale(1.12);
            box-shadow: 0 6px 14px rgba(0,0,0,0.18) !important;
            color: #AA7C11 !important;
        }
        .qw-offer-tag-badge {
            background: linear-gradient(135deg, #111111 0%, #2a220b 100%);
            color: var(--qw-gold);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 50rem;
            border: 1px solid rgba(212, 175, 55, 0.4);
            letter-spacing: 0.3px;
        }
        .qw-category-hero {
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 50%, #292008 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        /* Interactive Tap / Pulse Animated Button */
        .qw-tap-animated-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: linear-gradient(135deg, #111111 0%, #222222 100%);
            color: var(--qw-gold) !important;
            border: 1.5px solid var(--qw-gold);
            border-radius: 50rem;
            padding: 0.45rem 1.15rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 14px rgba(212, 175, 55, 0.25);
            cursor: pointer;
        }

        .qw-tap-animated-btn:hover {
            background: var(--qw-gold);
            color: #111111 !important;
            border-color: var(--qw-gold);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 8px 22px rgba(212, 175, 55, 0.45);
        }

        .qw-tap-finger-icon {
            font-size: 0.88rem;
            display: inline-block;
            animation: qwTapFinger 1.3s infinite ease-in-out;
        }

        @keyframes qwTapFinger {
            0%, 100% { transform: scale(1) translateY(0) rotate(0deg); }
            20% { transform: scale(0.82) translateY(2px) rotate(-12deg); }
            45% { transform: scale(1.2) translateY(-2px) rotate(6deg); }
            70% { transform: scale(0.95) translateY(1px) rotate(-2deg); }
        }

        .qw-tap-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--qw-gold);
            display: inline-block;
            position: relative;
        }
        .qw-tap-pulse-dot::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: var(--qw-gold);
            transform: translate(-50%, -50%);
            animation: qwTapPulse 1.6s infinite ease-out;
        }

        @keyframes qwTapPulse {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.9; }
            75% { transform: translate(-50%, -50%) scale(3.2); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 0; }
        }

        .qw-out-of-stock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            backdrop-filter: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            pointer-events: none;
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
            border-radius: 12px;
            overflow: hidden;
            height: 130px;
            border: 1px solid rgba(212, 175, 55, 0.22);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .qw-category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
            border-color: rgba(212, 175, 55, 0.6);
        }
        .qw-category-bg {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.75);
            transition: transform 0.5s ease, filter 0.5s ease;
        }
        .qw-category-card:hover .qw-category-bg {
            transform: scale(1.07);
            filter: brightness(0.62);
        }
        .qw-category-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 16px;
            text-align: center;
            background: linear-gradient(to top, rgba(15, 15, 15, 0.9) 0%, rgba(15, 15, 15, 0.4) 55%, rgba(15, 15, 15, 0.05) 100%);
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
            box-shadow: 0 4px 18px rgba(37, 211, 102, 0.4);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .qw-floating-whatsapp:hover {
            color: #FFF;
            transform: scale(1.08);
            box-shadow: 0 6px 24px rgba(37, 211, 102, 0.6);
        }

        /* Standard Footer */
        .qw-footer {
            background-color: var(--qw-black);
            color: var(--qw-white);
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
        @media (max-width: 991.98px) {
            .qw-header .btn-link { padding: 3px 5px !important; }
            .qw-header .btn-link i { font-size: 0.95rem !important; }
            .qw-header .cart-badge {
                top: -3px !important;
                right: -4px !important;
                width: 17px !important;
                height: 17px !important;
                font-size: 0.60rem !important;
            }
            .navbar-toggler { padding: 2px 4px !important; }
            .navbar-toggler-icon { width: 1.15em !important; height: 1.15em !important; }
        }
        @media (max-width: 767.98px) {
            .qw-logo-img { max-height: 52px !important; }
            .qw-share-btn-floating { width: 26px !important; height: 26px !important; border-radius: 6px !important; margin-right: 10px !important; }
            .qw-share-btn-floating i { font-size: 0.65rem !important; }
        }
        @media (max-width: 576px) {
            .qw-logo-img { max-height: 44px !important; }
            .qw-card-body { padding: 9px 10px !important; }
            .qw-category-card { height: 85px !important; border-radius: 8px !important; }
            .qw-category-overlay { padding: 6px 4px !important; }
            .qw-category-overlay h6 { font-size: 0.72rem !important; margin-bottom: 2px !important; }
            .qw-category-overlay .badge { font-size: 0.52rem !important; padding: 1px 4px !important; }
            .qw-floating-whatsapp { width: 38px; height: 38px; font-size: 20px; bottom: 14px; right: 14px; box-shadow: 0 3px 10px rgba(37, 211, 102, 0.4); }
            .qw-btn-card { font-size: 0.66rem !important; padding: 3px 6px !important; letter-spacing: 0.2px !important; }
            .purchase-action { font-size: 0.85rem !important; padding: 10px 14px !important; letter-spacing: 0.3px; }
            .qw-product-price { font-size: 0.92rem !important; }
            .qw-cut-price { font-size: 0.70rem !important; color: #dc3545 !important; }
            .qw-share-btn-floating { width: 24px !important; height: 24px !important; border-radius: 5px !important; margin-right: 8px !important; }
            .qw-share-btn-floating i { font-size: 0.60rem !important; }
            .qw-discount-badge {
                top: 6px !important;
                left: 6px !important;
                bottom: auto !important;
                font-size: 0.55rem !important;
                padding: 1px 5px !important;
                border-radius: 4px !important;
                letter-spacing: 0.2px !important;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
            }
            .qw-sort-filter-bar { width: 100% !important; }
            .qw-sort-form { flex: 0 0 74% !important; max-width: 74% !important; }
            .qw-filter-btn { flex: 0 0 calc(26% - 8px) !important; max-width: calc(26% - 8px) !important; padding-left: 0 !important; padding-right: 0 !important; }
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
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="qw-logo-img">
                </a>

                <div class="d-flex align-items-center d-lg-none ms-auto me-1 gap-0.5">
                    <button type="button" class="btn btn-link text-gold p-1" data-bs-toggle="modal" data-bs-target="#imageSearchModal" title="Visual Search / Search by Photo">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <a href="#" class="btn btn-link text-dark p-1" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn btn-link text-dark p-1 position-relative">
                        <i class="fa-solid fa-bag-shopping"></i>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>

                <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#qwNavbar">
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

                    <div class="d-none d-lg-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search by Text">
                            <i class="fa-solid fa-magnifying-glass text-dark"></i>
                        </button>

                        <button type="button" class="btn rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm d-flex align-items-center gap-1 border-0 text-white" data-bs-toggle="modal" data-bs-target="#imageSearchModal" style="background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);">
                            <i class="fa-solid fa-camera me-1"></i> Visual Search
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

    @if(($showLastLuckyDraw ?? '0') === '1' && isset($latestLuckyDraw) && $latestLuckyDraw && $latestLuckyDraw->winners->count() > 0)
        <!-- Last Lucky Winners Header Link Banner -->
        <div class="qw-lucky-winner-banner text-center py-2 px-3 position-relative" style="background: linear-gradient(135deg, #111111 0%, #2A2108 50%, #111111 100%); border-bottom: 1.5px solid var(--qw-gold);">
            <div class="container d-flex align-items-center justify-content-center flex-wrap gap-2">
                <span class="badge bg-gold text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                    <i class="fa-solid fa-crown me-1"></i> Winner Announcement
                </span>
                <a href="#" class="text-white text-decoration-none fw-bold small d-inline-flex align-items-center gap-1.5 qw-winner-link" data-bs-toggle="modal" data-bs-target="#latestLuckyWinnersModal" style="letter-spacing: 0.3px;">
                    <span class="text-gold"><i class="fa-solid fa-trophy text-warning"></i> Last Lucky Winners</span>
                    <span class="d-none d-sm-inline opacity-75">— Click here to view lucky winners!</span>
                    <span class="badge rounded-pill px-2 py-0.5 ms-1" style="background: rgba(212, 175, 55, 0.25); color: #F3E5AB; border: 1px solid rgba(212, 175, 55, 0.5); font-size: 0.7rem;">
                        <i class="fa-solid fa-eye me-1"></i> View Winners
                    </span>
                </a>
            </div>
        </div>
    @endif

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
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="mb-3" style="max-height: 50px;">
                    <p class="small text-muted mb-3">
                        {{ $siteName }} brings you affordable, high-fashion Korean tops, flattering western dresses, and trendy daily casual apparel. Dress beyond ordinary without breaking the bank.
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
                        <li><a href="{{ route('products.size-guide') }}"><i class="fa-solid fa-angle-right me-1"></i> Size Guide</a></li>
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
                    <p class="small text-muted mb-2"><i class="fa-solid fa-envelope text-gold me-2"></i> <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
                    <p class="small text-muted mb-2"><i class="fa-brands fa-whatsapp text-gold me-2"></i> Instant WhatsApp Care</p>
                    <p class="small text-muted"><i class="fa-solid fa-truck text-gold me-2"></i> Fast pan-India delivery</p>
                </div>
            </div>

            <hr class="my-4 border-secondary opacity-25">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted">
                <p class="mb-0">&copy; {{ date('Y') }} <strong>{{ $siteName }}</strong>. All Rights Reserved.</p>
                <p class="mb-0">Designed with Elegance & Affordability</p>
            </div>
        </div>
    </footer>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-serif fs-5 fw-bold">Search Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <form action="{{ route('shop') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control form-control-md border-end-0 rounded-start-pill" placeholder="Search tops, dresses, items..." required>
                            <button type="submit" class="btn btn-qw-gold rounded-end-pill px-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>

                    <div class="text-center pt-2 border-top">
                        <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#imageSearchModal" style="color: #b89327; border-color: #d4af37;">
                            <i class="fa-solid fa-camera me-1"></i> Search by Outfit Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('frontend.partials.lucky_winners_modal')
    @include('frontend.partials.image_search_modal')

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Globally prevent mouse wheel scrolling & touchpad micro-pan from changing number input values project-wide
        document.addEventListener('wheel', function (e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'number') {
                e.preventDefault();
                e.target.blur();
            }
        }, { passive: false });

        document.addEventListener('keydown', function (e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'number') {
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    e.preventDefault();
                }
            }
        });
    </script>
    <script src="{{ asset('js/pincode_autofill.js') }}"></script>
    @yield('scripts')
</body>
</html>
