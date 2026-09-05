<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Lucky Winner') · {{ $siteName }}</title>
    <link rel="icon" href="{{ $siteFaviconUrl }}">
    <link rel="stylesheet" href="{{ asset('css/luckywinner.css') }}?v=1">
</head>
<body class="lw-body">
    <header class="lw-header">
        <a class="lw-brand" href="{{ route('luckywinner.index') }}">
            <span class="lw-logo"><img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}"></span>
            <span><strong>{{ $siteName }}</strong><small>THE CUSTOMER GIVEAWAY</small></span>
        </a>
        <nav aria-label="Lucky Winner navigation">
            <a class="{{ request()->routeIs('luckywinner.index') ? 'active' : '' }}" href="{{ route('luckywinner.index') }}">Draw studio</a>
            <a class="lw-admin-link" href="{{ route('admin.dashboard') }}">Admin <span aria-hidden="true">↗</span></a>
        </nav>
    </header>
    <main class="lw-main">@yield('content')</main>
    <footer class="lw-footer"><span>{{ $siteName }} · A little thank you. A little luck.</span><span>EXCLUSIVELY FOR OUR CUSTOMERS</span></footer>
    @yield('scripts')
</body>
</html>
