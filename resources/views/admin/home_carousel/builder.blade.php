<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carousel Builder - Quara Wardrobe Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ $siteFaviconUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&family=Lora:ital,wght@0,400;0,600;1,400&family=Merriweather:wght@400;700&family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600;700&family=Outfit:wght@400;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #0d0e11;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #ffffff;
        }

        .qw-builder-app {
            display: flex;
            flex-direction: column;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #0d0e11;
        }

        .qw-builder-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: #18191c;
            border-bottom: 1px solid #282a30;
            height: 54px;
            flex-shrink: 0;
            z-index: 100;
        }

        .qw-brand-title {
            font-weight: 800;
            font-size: 1.05rem;
            color: #f0c75e;
        }

        .qw-brand-sub {
            font-size: 0.75rem;
            color: #8e94a0;
            margin-left: 8px;
            border-left: 1px solid #3d414a;
            padding-left: 8px;
        }

        .qw-builder-body {
            display: flex;
            flex: 1;
            height: calc(100vh - 54px);
            overflow: hidden;
        }

        .qw-sidebar-wrapper {
            display: flex;
            width: 200px;
            background: #141619;
            border-right: 1px solid #26282e;
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            flex-shrink: 0;
        }

        .qw-sidebar-wrapper.collapsed {
            width: 44px;
        }

        .qw-sidebar-wrapper.collapsed .qw-drawer-panel {
            display: none !important;
        }

        .qw-nav-icons {
            display: flex;
            flex-direction: column;
            width: 44px;
            background: #101114;
            border-right: 1px solid #22242a;
            padding: 4px 0;
            flex-shrink: 0;
        }

        .qw-nav-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            width: 100%;
            height: 42px;
            background: none;
            border: 0;
            color: #888d9a;
            font-size: 0.54rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.15s;
        }

        .qw-nav-btn i {
            font-size: 0.88rem;
        }

        .qw-nav-btn:hover, .qw-nav-btn.active {
            color: #f0c75e;
            background: #1c1e23;
        }

        .qw-drawer-panel {
            flex: 1;
            padding: 8px;
            overflow-y: auto;
            background: #141619;
        }

        .qw-drawer-title {
            font-size: 0.64rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8e94a0;
            margin-bottom: 6px;
        }

        .qw-tab-content {
            display: none;
        }

        .qw-tab-content.active {
            display: block;
        }

        .qw-grid-tile {
            width: 100%;
            height: 44px;
            background: #1f2228;
            border: 1px solid #2d313a;
            border-radius: 6px;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            font-size: 0.62rem;
            cursor: grab;
            transition: 0.15s;
            user-select: none;
        }

        .qw-grid-tile:hover {
            background: #292d37;
            border-color: #f0c75e;
            color: #f0c75e;
        }

        .qw-text-add-btn {
            width: 100%;
            padding: 5px 8px;
            background: #1f2228;
            border: 1px solid #2d313a;
            border-radius: 6px;
            color: #fff;
            text-align: left;
            font-size: 0.68rem;
            cursor: grab;
            transition: 0.15s;
            user-select: none;
        }

        .qw-text-add-btn:hover {
            background: #292d37;
            border-color: #f0c75e;
        }

        .qw-preset-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .qw-preset-item {
            padding: 5px;
            background: #1f2228;
            border-radius: 6px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        .qw-badge-preview {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.65rem;
        }

        .qw-badge-preview.pink-pill { background: #ff6b8b; color: #fff; }
        .qw-badge-preview.red-ribbon { background: #d92338; color: #fff; letter-spacing: 1px; }
        .qw-badge-preview.gold-brush { background: #f0c75e; color: #21180a; }
        .qw-badge-preview.stamp-circle { border: 2px dashed #ff9800; color: #ff9800; border-radius: 50px; }
        .qw-badge-preview.delivery-box { background: #10b981; color: #fff; }
        .qw-badge-preview.maroon-btn { background: #6b1e3f; color: #fff; }
        .qw-badge-preview.promo-flag { background: #8b5cf6; color: #fff; }

        .qw-template-cards {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .qw-template-card {
            background: #1f2228;
            border-radius: 8px;
            padding: 6px;
            cursor: pointer;
            border: 1px solid #2d313a;
            font-size: 0.68rem;
        }

        .qw-template-thumb {
            height: 75px;
            border-radius: 5px;
            padding: 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }

        .qw-canvas-stage {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #090a0c;
            position: relative;
            overflow: hidden;
        }

        .qw-canvas-viewport {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            overflow: auto;
        }

        .qw-canvas-frame {
            position: relative;
            width: 1080px;
            height: 450px;
            background: #faf7f2;
            border-radius: 14px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.7);
            overflow: hidden;
            user-select: none;
            transform-origin: center center;
            transition: width 0.3s ease, height 0.3s ease, border-radius 0.3s ease, transform 0.2s ease;
        }

        .qw-canvas-frame.mobile-mode {
            width: 360px !important;
            height: 480px !important;
            border-radius: 14px !important;
            box-shadow: 0 0 0 8px #1e2026, 0 25px 70px rgba(0,0,0,0.85) !important;
            border: 3px solid #3d414a !important;
        }

        .qw-no-mobile-badge {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background: rgba(220, 53, 69, 0.95);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            pointer-events: none;
            white-space: nowrap;
        }

        .qw-canvas-frame img#canvasBgImage {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            transition: opacity .45s ease, transform .65s ease;
        }

        .qw-canvas-frame.is-default-preview img#canvasBgImage { opacity: .35; transform: scale(1.035); }
        .qw-canvas-frame.is-default-preview .qw-canvas-elements-layer { opacity: 0; }
        .qw-canvas-frame.is-default-preview .qw-canvas-arrow { pointer-events: auto; cursor: pointer; }
        .qw-canvas-frame.is-default-preview .qw-canvas-dots { pointer-events: auto; }
        .qw-canvas-frame.is-default-preview .qw-canvas-dots span { cursor: pointer; transition: all .25s ease; }
        .qw-canvas-preview-title { position:absolute; z-index:3; left:8%; bottom:18%; max-width:75%; color:#fff; font:700 clamp(22px,4vw,48px) Georgia,serif; text-shadow:0 2px 12px #000; opacity:0; transform:translateY(12px); transition:opacity .35s ease,transform .35s ease; pointer-events:none; }
        .qw-canvas-preview-title.visible { opacity:1; transform:translateY(0); }

        .qw-canvas-elements-layer {
            position: absolute;
            inset: 0;
            z-index: 2;
        }

        .qw-canvas-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            color: #fff;
            display: grid;
            place-items: center;
            z-index: 4;
            pointer-events: none;
        }

        .qw-canvas-arrow.left-arrow { left: 12px; }
        .qw-canvas-arrow.right-arrow { right: 12px; }

        .qw-canvas-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 4;
            pointer-events: none;
        }

        .qw-canvas-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
        }

        .qw-canvas-dots span.active {
            width: 22px;
            border-radius: 10px;
            background: #fff;
        }

        .qw-mobile-guide {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 340px;
            border: 2px dashed #ff6b8b;
            z-index: 5;
            pointer-events: none;
            background: rgba(255,107,139,0.05);
        }

        .qw-mobile-safe-label {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            background: #ff6b8b;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .qw-slide-manager-strip {
            height: 95px;
            background: #141619;
            border-top: 1px solid #26282e;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            flex-shrink: 0;
        }

        .qw-slide-thumbs-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .qw-slide-thumb-card {
            width: 110px;
            height: 68px;
            background: #1f2228;
            border: 2px solid #2d313a;
            border-radius: 6px;
            padding: 2px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 2px;
            transition: 0.15s;
            position: relative;
        }

        .qw-slide-thumb-card.is-dragging { opacity: .45; }
        .qw-slide-thumb-card.drop-target { border-color: #6bb4ff; transform: scale(1.04); }
        .qw-thumb-delete { position:absolute; z-index:3; top:2px; right:2px; width:20px; height:20px; padding:0; display:grid; place-items:center; border:0; border-radius:50%; background:#a92335e8; color:#fff; font-size:10px; cursor:pointer; }
        .qw-thumb-delete:hover { background:#dc3545; }
        .qw-thumb-edit { position:absolute; z-index:3; top:2px; right:24px; width:20px; height:20px; padding:0; display:grid; place-items:center; border:0; border-radius:50%; background:#0d6efde8; color:#fff; font-size:10px; cursor:pointer; }
        .qw-thumb-edit:hover { background:#0b5ed7; }

        .qw-slide-thumb-card.active, .qw-slide-thumb-card:hover {
            border-color: #f0c75e;
            background: #282c35;
        }

        .qw-thumb-img-wrap {
            position: relative;
            height: 42px;
            border-radius: 4px;
            overflow: hidden;
            background: #0d0e11;
        }

        .qw-thumb-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .qw-thumb-num {
            position: absolute;
            bottom: 2px;
            left: 2px;
            background: rgba(0,0,0,0.7);
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            padding: 1px 3px;
            border-radius: 3px;
        }

        .qw-thumb-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2px;
        }

        .qw-thumb-title {
            font-size: 8px;
            font-weight: 700;
            color: #a0a6b5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80px;
        }

        .qw-add-slide-card {
            width: 85px;
            height: 68px;
            border: 2px dashed #343844;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #8e94a0;
            cursor: pointer;
            transition: 0.15s;
        }

        .qw-add-slide-card:hover {
            border-color: #f0c75e;
            color: #f0c75e;
            background: #1c1e23;
        }

        /* ULTRA COMPACT RIGHT INSPECTOR PANEL */
        .qw-inspector-panel {
            width: 170px;
            background: #141619;
            border-left: 1px solid #26282e;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .qw-inspector-header {
            padding: 8px 10px;
            border-bottom: 1px solid #26282e;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .qw-inspector-header h6 {
            font-size: 0.72rem !important;
        }

        .qw-inspector-body {
            padding: 8px 10px;
            overflow-y: auto;
            flex: 1;
            font-size: 0.68rem;
        }

        .qw-inspector-body label {
            font-size: 0.60rem !important;
            margin-bottom: 2px !important;
        }

        .qw-element-item {
            position: absolute;
            cursor: move;
            user-select: none;
            touch-action: none;
            display: inline-block;
            line-height: 1.2;
            padding: 4px 8px;
            border: 1px transparent dashed;
            border-radius: 4px;
        }

        .qw-element-item.selected {
            border-color: #3b82f6;
            outline: 2px solid #3b82f6;
            box-shadow: 0 0 12px rgba(59,130,246,0.5);
        }

        .qw-element-item .qw-resize-handle {
            position: absolute;
            z-index: 10;
            width: 10px;
            height: 10px;
            padding: 0;
            border: 1px solid #1d4ed8;
            border-radius: 2px;
            background: #fff;
            touch-action: none;
        }
        .qw-resize-handle[data-resize="nw"] { top: -6px; left: -6px; cursor: nwse-resize; }
        .qw-resize-handle[data-resize="ne"] { top: -6px; right: -6px; cursor: nesw-resize; }
        .qw-resize-handle[data-resize="se"] { right: -6px; bottom: -6px; cursor: nwse-resize; }
        .qw-resize-handle[data-resize="sw"] { bottom: -6px; left: -6px; cursor: nesw-resize; }
        .qw-resize-handle[data-resize="n"] { top: -6px; left: calc(50% - 5px); cursor: ns-resize; }
        .qw-resize-handle[data-resize="s"] { bottom: -6px; left: calc(50% - 5px); cursor: ns-resize; }

        /* Form Controls Dark Theme - Compact Sizing */
        .form-control, .form-select {
            background-color: #1f2228 !important;
            border-color: #2d313a !important;
            color: #ffffff !important;
            font-size: 0.68rem !important;
            padding: 2px 5px !important;
            height: 26px !important;
            border-radius: 4px !important;
        }

        .qw-inspector-body textarea.form-control {
            height: auto !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #f0c75e !important;
            box-shadow: 0 0 0 0.15rem rgba(240, 199, 94, 0.25) !important;
        }

        .form-control-color {
            padding: 1px !important;
            height: 24px !important;
        }

        @media (max-width: 1050px) {
            .qw-builder-navbar { height: auto; min-height: 54px; flex-wrap: wrap; gap: 6px; padding: 6px 10px; }
            .qw-builder-body { height: calc(100vh - 90px); }
            .qw-navbar-left, .qw-navbar-center, .qw-navbar-right { display: flex; align-items: center; flex-wrap: wrap; gap: 5px; }
            .qw-navbar-left { flex: 1 1 240px; }
            .qw-navbar-center { flex: 1 1 260px; justify-content: center; }
            .qw-navbar-right { flex: 1 1 100%; justify-content: flex-end; }
            .qw-navbar-left .me-3 { margin-right: .35rem !important; }
        }

        @media (max-width: 600px) {
            .qw-builder-body { height: calc(100vh - 118px); }
            .qw-brand-title { font-size: .8rem; }
            .qw-brand-sub { font-size: .62rem; }
            .qw-navbar-left { flex-basis: 100%; }
            .qw-navbar-center { flex-basis: 100%; justify-content: flex-start; }
            .qw-navbar-right { flex-basis: 100%; justify-content: flex-start; }
            .qw-navbar-right .btn { padding-inline: .55rem !important; font-size: .65rem; }
            .qw-sidebar-wrapper { width: 145px; }
            .qw-sidebar-wrapper.collapsed { width: 44px; }
            .qw-drawer-panel { min-width: 101px; padding: 5px; }
            .qw-inspector-panel { width: 132px; }
            .qw-inspector-body { padding: 6px; }
            .qw-canvas-viewport { padding: 8px 4px; }
            .qw-slide-manager-strip { height: 76px; padding: 4px 6px; }
            .qw-slide-thumb-card { flex-shrink: 0; }
        }
    </style>
</head>
<body>

<div class="qw-builder-app">
    <!-- TOP TOOLBAR -->
    <header class="qw-builder-navbar">
        <div class="qw-navbar-left">
            <a href="{{ route('admin.home-carousel.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 me-3" title="Return to Homepage Carousel Master">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Master Page
            </a>
            <div class="qw-brand">
                <span class="qw-brand-title"><i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> Quara Wardrobe</span>
                <span class="qw-brand-sub">Carousel Builder</span>
            </div>
        </div>

        <div class="qw-navbar-center">
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-2" id="btnDefaultPreview" title="Preview the carousel with its default slide animation"><i class="fa-solid fa-play me-1"></i> Default</button>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-dark" id="btnUndo" title="Undo (Ctrl+Z)"><i class="fa-solid fa-rotate-left"></i></button>
                <button type="button" class="btn btn-sm btn-dark" id="btnRedo" title="Redo (Ctrl+Y)"><i class="fa-solid fa-rotate-right"></i></button>
            </div>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-dark" id="btnZoomOut" title="Zoom Out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                <span class="btn btn-sm btn-dark text-muted fw-bold px-2" id="zoomLabel">100%</span>
                <button type="button" class="btn btn-sm btn-dark" id="btnZoomIn" title="Zoom In"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-dark active" id="btnViewDesktop" title="Desktop View (1440x600)"><i class="fa-solid fa-desktop me-1"></i> Desktop</button>
                <button type="button" class="btn btn-sm btn-dark" id="btnViewMobile" title="Mobile View Preview"><i class="fa-solid fa-mobile-screen me-1"></i> Mobile</button>
            </div>
        </div>

        <div class="qw-navbar-right">
            <label class="form-check form-switch text-light small mb-0 me-2 d-flex align-items-center gap-2" title="Show or hide the hero carousel on the homepage">
                <input class="form-check-input mt-0" type="checkbox" id="heroCarouselToggle" {{ $settings->enabled && ($sections->get('hero')?->enabled ?? true) ? 'checked' : '' }}>
                <span>Hero carousel</span>
            </label>
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" id="btnSaveDraft"><i class="fa-regular fa-bookmark me-1"></i> Save Draft</button>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-warning rounded-pill px-3" id="btnLivePreview"><i class="fa-regular fa-eye me-1"></i> Live Site</a>
            <button type="button" class="btn btn-sm btn-danger rounded-pill fw-bold px-4 shadow-sm" id="btnPublish"><i class="fa-solid fa-paper-plane me-1"></i> Publish Slide</button>
        </div>
    </header>

    <!-- MAIN EDITOR CONTAINER -->
    <div class="qw-builder-body">
        
        <!-- SECTION 1: LEFT SIDEBAR TABS & DRAWER -->
        <div class="qw-sidebar-wrapper">
            <!-- Icon Nav Bar -->
            <div class="qw-nav-icons">
                <button type="button" class="qw-nav-btn active" data-tab="elements"><i class="fa-solid fa-shapes"></i><span>Elements</span></button>
                <button type="button" class="qw-nav-btn" data-tab="text"><i class="fa-solid fa-font"></i><span>Text</span></button>
                <button type="button" class="qw-nav-btn" data-tab="templates"><i class="fa-solid fa-table-cells-large"></i><span>Templates</span></button>
                <button type="button" class="qw-nav-btn" data-tab="images"><i class="fa-regular fa-image"></i><span>Images</span></button>
                <button type="button" class="qw-nav-btn" data-tab="buttons"><i class="fa-solid fa-rectangle-ad"></i><span>Buttons</span></button>
                <button type="button" class="qw-nav-btn" data-tab="shapes"><i class="fa-solid fa-vector-square"></i><span>Shapes</span></button>
                <button type="button" class="qw-nav-btn" data-tab="icons"><i class="fa-solid fa-icons"></i><span>Icons</span></button>
                <button type="button" class="qw-nav-btn" data-tab="badges"><i class="fa-solid fa-certificate"></i><span>Badges</span></button>
                <button type="button" class="qw-nav-btn" data-tab="background"><i class="fa-solid fa-fill-drip"></i><span>Background</span></button>
                <button type="button" class="qw-nav-btn" data-tab="upload"><i class="fa-solid fa-cloud-arrow-up"></i><span>Upload</span></button>
                <button type="button" class="qw-nav-btn" data-tab="layers"><i class="fa-solid fa-layer-group"></i><span>Layers</span></button>
                <button type="button" class="qw-nav-btn mt-auto text-warning" id="btnToggleSidebar" title="Collapse / Expand Sidebar"><i class="fa-solid fa-angles-left" id="iconToggleSidebar"></i><span id="textToggleSidebar">Collapse</span></button>
            </div>

            <!-- Drawer Content Panel -->
            <div class="qw-drawer-panel">
                
                <!-- ELEMENTS TAB -->
                <div class="qw-tab-content active" id="tab-elements">
                    <h6 class="qw-drawer-title">Add Elements</h6>
                    <div class="row g-2 mb-4">
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileText"><i class="fa-solid fa-font"></i><span>Text</span></button></div>
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileImage"><i class="fa-regular fa-image"></i><span>Image</span></button></div>
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileButton"><i class="fa-solid fa-rectangle-ad"></i><span>Button</span></button></div>
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileShape"><i class="fa-solid fa-square"></i><span>Shape</span></button></div>
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileIcon"><i class="fa-solid fa-star"></i><span>Icon</span></button></div>
                        <div class="col-6"><button type="button" class="qw-grid-tile" id="addTileBadge"><i class="fa-solid fa-certificate"></i><span>Badge</span></button></div>
                    </div>

                    <h6 class="qw-drawer-title">Ready Design Elements</h6>
                    <div class="qw-preset-list">
                        <div class="qw-preset-item" data-preset="new_pink">
                            <span class="qw-badge-preview pink-pill">New</span>
                        </div>
                        <div class="qw-preset-item" data-preset="sale_red">
                            <span class="qw-badge-preview red-ribbon">SALE</span>
                        </div>
                        <div class="qw-preset-item" data-preset="bestseller_gold">
                            <span class="qw-badge-preview gold-brush">BEST SELLER</span>
                        </div>
                        <div class="qw-preset-item" data-preset="limited_stock">
                            <span class="qw-badge-preview stamp-circle">LIMITED STOCK</span>
                        </div>
                        <div class="qw-preset-item" data-preset="free_delivery">
                            <span class="qw-badge-preview delivery-box"><i class="fa-solid fa-truck me-1"></i> ₹300+ FREE Delivery</span>
                        </div>
                        <div class="qw-preset-item" data-preset="shop_now_btn">
                            <span class="qw-badge-preview maroon-btn">SHOP NOW &rarr;</span>
                        </div>
                        <div class="qw-preset-item" data-preset="flat_50">
                            <span class="qw-badge-preview promo-flag">Flat 50% OFF</span>
                        </div>
                    </div>
                </div>

                <!-- TEXT TAB -->
                <div class="qw-tab-content" id="tab-text">
                    <h6 class="qw-drawer-title">Add Text Elements</h6>
                    <button type="button" class="qw-text-add-btn text-heading mb-2" id="btnAddHeading">Add Heading (Playfair)</button>
                    <button type="button" class="qw-text-add-btn text-subheading mb-2" id="btnAddSubheading">Add Sub Heading (Lora)</button>
                    <button type="button" class="qw-text-add-btn text-paragraph mb-2" id="btnAddParagraph">Add Paragraph (Inter)</button>
                    <button type="button" class="qw-text-add-btn text-small mb-4" id="btnAddSmallText">Add Small Text</button>

                    <h6 class="qw-drawer-title">Text Style Presets</h6>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-outline-light text-start btn-sm font-playfair" id="btnPresetLuxury">Luxury Fashion Heading</button>
                        <button type="button" class="btn btn-outline-light text-start btn-sm font-montserrat" id="btnPresetBoldPromo">Bold Discount Offer</button>
                        <button type="button" class="btn btn-outline-light text-start btn-sm font-lora" id="btnPresetItalicSubtitle">Curated Collection Subtitle</button>
                    </div>
                </div>

                <!-- TEMPLATES TAB -->
                <div class="qw-tab-content" id="tab-templates">
                    <h6 class="qw-drawer-title">Quick Start Templates</h6>
                    <button type="button" class="btn btn-sm btn-outline-warning w-100 mb-2" id="btnGenerateSample">Generate sample text and button</button>
                    <p class="small text-secondary">Only adds sample content when you choose it.</p>
                    <div class="qw-template-cards">
                        <div class="qw-template-card" data-template="new_arrivals">
                            <div class="qw-template-thumb bg-blush">
                                <span class="badge bg-danger mb-1">New Arrivals</span>
                                <h6>Trendy Styles for Every You</h6>
                                <span class="btn btn-xs btn-dark">Shop Now</span>
                            </div>
                            <span>New Arrivals Fashion</span>
                        </div>
                        <div class="qw-template-card" data-template="summer_sale">
                            <div class="qw-template-thumb bg-maroon text-white">
                                <span class="badge bg-warning text-dark mb-1">FLAT 50% OFF</span>
                                <h6>Summer Fashion Sale</h6>
                                <span class="btn btn-xs btn-light">Explore</span>
                            </div>
                            <span>Summer Sale Banner</span>
                        </div>
                    </div>
                </div>

                <!-- IMAGES TAB -->
                <div class="qw-tab-content" id="tab-images">
                    <h6 class="qw-drawer-title">Background & Slide Image</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Desktop Image Preview</label>
                        <div class="qw-tab-img-preview mb-2 p-1 rounded border border-secondary text-center bg-black">
                            <img id="tabPreviewDesktop" src="{{ $slide?->image_url }}" alt="Desktop Image" class="img-fluid rounded" style="max-height:110px; object-fit:contain;" @if(!$slide?->image_url) hidden @endif>
                            <span id="tabPreviewDesktopText" class="small text-muted @if($slide?->image_url) d-none @endif"><i class="fa-regular fa-image me-1"></i> No Desktop Image</span>
                        </div>
                        <label class="form-label small fw-bold">Upload New Desktop Image</label>
                        <input type="file" id="inputSlideImage" class="form-control form-control-sm" accept="image/*">
                        <small class="d-block text-muted mt-1">1500 × 1000 px (Landscape) recommended for desktop view.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-warning"><i class="fa-solid fa-mobile-screen me-1"></i> Mobile Image Preview</label>
                        <div class="qw-tab-img-preview mb-2 p-1 rounded border border-secondary text-center bg-black">
                            <img id="tabPreviewMobile" src="{{ $slide?->mobile_image_url }}" alt="Mobile Image" class="img-fluid rounded" style="max-height:110px; object-fit:contain;" @if(!$slide?->has_mobile_image) hidden @endif>
                            <span id="tabPreviewMobileText" class="small text-muted @if($slide?->has_mobile_image) d-none @endif"><i class="fa-solid fa-mobile-screen me-1"></i> No Separate Mobile Image</span>
                        </div>
                        <label class="form-label small fw-bold text-warning">Upload New Mobile Image</label>
                        <input type="file" id="inputMobileSlideImage" class="form-control form-control-sm" accept="image/*">
                        <small class="d-block text-muted mt-1">1080 × 1080 px (Square) or 1080 × 1350 px (Portrait) recommended for mobile view.</small>
                    </div>
                </div>

                <!-- BUTTONS TAB -->
                <div class="qw-tab-content" id="tab-buttons">
                    <h6 class="qw-drawer-title">Button Presets</h6>
                    <div class="d-flex flex-column gap-3">
                        <button type="button" class="qw-btn-preset maroon-pill" data-btn-style="maroon">SHOP NOW &rarr;</button>
                        <button type="button" class="qw-btn-preset gold-pill" data-btn-style="gold">EXPLORE COLLECTION</button>
                        <button type="button" class="qw-btn-preset outline-dark-pill" data-btn-style="outline">DISCOVER MORE</button>
                    </div>
                </div>

                <!-- SHAPES TAB -->
                <div class="qw-tab-content" id="tab-shapes">
                    <h6 class="qw-drawer-title">Decorative Shapes</h6>
                    <div class="row g-2">
                        <div class="col-4"><button type="button" class="qw-shape-btn" data-shape="rect"><i class="fa-regular fa-square fs-3"></i><span>Rectangle</span></button></div>
                        <div class="col-4"><button type="button" class="qw-shape-btn" data-shape="rounded"><i class="fa-solid fa-square-full fs-3 rounded-3"></i><span>Card</span></button></div>
                        <div class="col-4"><button type="button" class="qw-shape-btn" data-shape="circle"><i class="fa-regular fa-circle fs-3"></i><span>Circle</span></button></div>
                    </div>
                </div>

                <!-- ICONS TAB -->
                <div class="qw-tab-content" id="tab-icons">
                    <h6 class="qw-drawer-title">Icon Library</h6>
                    <div class="qw-icon-grid">
                        <button type="button" class="qw-icon-tile" data-icon="fa-heart"><i class="fa-solid fa-heart"></i></button>
                        <button type="button" class="qw-icon-tile" data-icon="fa-truck"><i class="fa-solid fa-truck"></i></button>
                        <button type="button" class="qw-icon-tile" data-icon="fa-star"><i class="fa-solid fa-star"></i></button>
                        <button type="button" class="qw-icon-tile" data-icon="fa-bag-shopping"><i class="fa-solid fa-bag-shopping"></i></button>
                    </div>
                </div>

                <!-- BADGES TAB -->
                <div class="qw-tab-content" id="tab-badges">
                    <h6 class="qw-drawer-title">E-commerce Badges</h6>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-danger text-white rounded-pill fw-bold" id="btnAddBadgeSale">SALE - 50% OFF</button>
                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold" id="btnAddBadgeBestseller">★ BEST SELLER</button>
                    </div>
                </div>

                <!-- BACKGROUND TAB -->
                <div class="qw-tab-content" id="tab-background">
                    <h6 class="qw-drawer-title">Canvas Background</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Solid Fill Color</label>
                        <input type="color" id="canvasBgColor" class="form-control form-control-color w-100" value="#faf7f2">
                    </div>
                </div>

                <!-- UPLOAD TAB -->
                <div class="qw-tab-content" id="tab-upload">
                    <h6 class="qw-drawer-title">Upload Custom Media</h6>
                    <div class="qw-upload-dropzone text-center p-3 rounded-3 border-dashed">
                        <i class="fa-solid fa-cloud-arrow-up fs-2 text-muted mb-2"></i>
                        <input type="file" id="inputCustomUpload" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>

                <!-- LAYERS TAB -->
                <div class="qw-tab-content" id="tab-layers">
                    <h6 class="qw-drawer-title">Canvas Layer Ordering</h6>
                    <div class="qw-layer-list" id="layerListContainer">
                        <p class="text-muted small text-center py-3 mb-0">No elements selected</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 2: CENTER STAGE - CAROUSEL CANVAS -->
        <main class="qw-canvas-stage">
            <div class="qw-canvas-viewport" id="canvasViewport">
                <!-- Outer Aspect Frame matching Canva Canvas -->
                <div class="qw-canvas-frame" id="canvasFrame">
                    <!-- Slide Background Image Layer -->
                    <img id="canvasBgImage" src="{{ $slide?->image_url ?: '' }}" alt="Background slide" @if(!$slide?->image_url) hidden @endif>
                    
                    <!-- Decorative Canvas SVG Overlay -->
                    <div class="qw-canvas-art-layer" id="canvasArtLayer"></div>

                    <!-- Interactive Elements Container Layer -->
                    <div class="qw-canvas-elements-layer" id="canvasElementsLayer"></div>
                    <div class="qw-canvas-preview-title" id="canvasPreviewTitle"></div>
                    
                    <!-- Navigation Arrows Preview -->
                    <div class="qw-canvas-arrow left-arrow"><i class="fa-solid fa-chevron-left"></i></div>
                    <div class="qw-canvas-arrow right-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                    
                    <!-- Pagination Dots Preview -->
                    <div class="qw-canvas-dots" id="canvasPreviewDots">
                        @foreach($slides->where('section_key', $builderSection) as $index => $previewSlide)
                            <span class="{{ $index === 0 ? 'active' : '' }}" data-preview-index="{{ $index }}"></span>
                        @endforeach
                    </div>

                    <!-- Mobile View Bounds Guide Overlay -->
                    <div class="qw-mobile-guide d-none" id="mobileGuide">
                        <div class="qw-mobile-safe-label">Mobile Safe Zone</div>
                    </div>
                    <div class="qw-no-mobile-badge d-none" id="noMobileNotice">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> No mobile image uploaded (showing desktop fallback)
                    </div>
                </div>
            </div>

            <!-- BOTTOM SLIDE MANAGER STRIP -->
            <div class="qw-slide-manager-strip">
                <div class="d-flex justify-content-between align-items-center me-2">
                    <span class="fw-bold small text-light"><i class="fa-solid fa-film me-1 text-warning"></i> Slides ({{ $slides->where('section_key', $builderSection)->count() }}) <span class="text-secondary fw-normal">Drag to reorder</span></span>
                </div>
                <div class="qw-slide-thumbs-wrapper" id="slideThumbsList">
                    @foreach($slides->where('section_key', $builderSection)->values() as $index => $item)
                        <div class="qw-slide-thumb-card {{ ($slide?->id === $item->id) ? 'active' : '' }}" data-slide-id="{{ $item->id }}" data-slide-url="{{ route('admin.home-carousel.builder', $item->id) }}" draggable="true">
                            <div class="qw-thumb-img-wrap">
                                <img src="{{ $item->image_url }}" alt="Slide {{ $index+1 }}">
                                <span class="qw-thumb-num">{{ $index+1 }}</span>
                                <button type="button" class="qw-thumb-edit" data-slide-id="{{ $item->id }}" data-heading="{{ $item->heading }}" data-desktop-url="{{ $item->image_url }}" data-mobile-url="{{ $item->has_mobile_image ? $item->mobile_image_url : '' }}" data-has-mobile="{{ $item->has_mobile_image ? '1' : '0' }}" title="Edit slide images and title"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="qw-thumb-delete" data-delete-url="{{ route('admin.home-carousel.slides.destroy', $item->id) }}" aria-label="Delete {{ $item->heading ?: 'slide ' . ($index + 1) }}" title="Delete slide"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                            <div class="qw-thumb-info">
                                <span class="qw-thumb-title">{{ $item->heading ?: 'Slide #' . ($index+1) }}</span>
                            </div>
                        </div>
                    @endforeach

                    <!-- Add Slide Button Card -->
                    <div class="qw-add-slide-card" id="btnAddSlideModal">
                        <i class="fa-solid fa-plus fs-5 text-warning mb-1"></i>
                        <span class="fw-semibold small">Add Slide</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- SECTION 3: RIGHT SIDEBAR - INSPECTOR / TEXT SETTINGS -->
        <aside class="qw-inspector-panel">
            <div class="qw-inspector-header">
                <h6 class="fw-bold mb-0 text-light" id="inspectorTitle">Text Settings</h6>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" id="btnDeleteSelectedElement" title="Delete selected element"><i class="fa-solid fa-trash-can"></i></button>
            </div>

            <div class="qw-inspector-body" id="inspectorContent">
                
                <!-- TEXT CONTENT FIELD -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted" for="propTextContent">Text Content</label>
                    <textarea id="propTextContent" class="form-control form-control-sm" rows="2" placeholder="Enter text content..."></textarea>
                </div>

                <!-- FONT FAMILY -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted" for="propFontFamily">Font Family</label>
                    <select id="propFontFamily" class="form-select form-select-sm">
                        <option value="Playfair Display">Playfair Display (Serif)</option>
                        <option value="Lora">Lora (Elegant Serif)</option>
                        <option value="Cormorant Garamond">Cormorant Garamond</option>
                        <option value="Inter">Inter (Clean Sans)</option>
                        <option value="Poppins">Poppins (Modern)</option>
                        <option value="Montserrat">Montserrat (Bold)</option>
                        <option value="Roboto">Roboto</option>
                        <option value="Open Sans">Open Sans</option>
                    </select>
                </div>

                <!-- TEXT ALIGNMENT -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted" for="propTextAlign">Text Alignment</label>
                    <select id="propTextAlign" class="form-select form-select-sm">
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </div>

                <!-- FONT SIZE & WEIGHT -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propFontSize">Size (px)</label>
                        <input type="number" id="propFontSize" class="form-control form-control-sm" min="10" max="140" value="40">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propFontWeight">Weight</label>
                        <select id="propFontWeight" class="form-select form-select-sm">
                            <option value="300">Light</option>
                            <option value="400">Regular</option>
                            <option value="500">Medium</option>
                            <option value="600">Semi Bold</option>
                            <option value="700" selected>Bold</option>
                            <option value="800">Extra Bold</option>
                        </select>
                    </div>
                </div>

                <!-- ELEMENT DIMENSIONS -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propElementWidth">Width (px)</label>
                        <input type="number" id="propElementWidth" class="form-control form-control-sm" min="0" max="1080" step="1" placeholder="Auto">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propElementHeight">Height (px)</label>
                        <input type="number" id="propElementHeight" class="form-control form-control-sm" min="0" max="450" step="1" placeholder="Auto">
                    </div>
                    <div class="col-12"><small class="text-muted">Set to 0 for automatic size.</small></div>
                </div>

                <!-- COLORS -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propTextColor">Text Color</label>
                        <div class="input-group input-group-sm">
                            <input type="color" id="propTextColor" class="form-control form-control-color" value="#6B1E3F">
                            <input type="text" id="propTextColorHex" class="form-control text-uppercase" value="#6B1E3F">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted" for="propBgColor">Badge / Button</label>
                        <div class="input-group input-group-sm">
                            <input type="color" id="propBgColor" class="form-control form-control-color" value="#f0c75e">
                            <input type="text" id="propBgColorHex" class="form-control text-uppercase" value="#F0C75E">
                        </div>
                    </div>
                </div>

                <!-- LINK SYSTEM -->
                <div class="card border border-secondary rounded-3 p-2 bg-dark mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="propHasLink">
                        <label class="form-check-label fw-bold small text-light" for="propHasLink"><i class="fa-solid fa-link me-1 text-warning"></i> Add Link</label>
                    </div>
                    <div id="linkFieldsWrap" class="d-none">
                        <input type="text" id="propLinkUrl" class="form-control form-control-sm mb-2" placeholder="https://... or /shop">
                    </div>
                </div>

                <!-- ANIMATION & RADIUS -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted" for="propAnimation">Animation</label>
                    <select id="propAnimation" class="form-select form-select-sm">
                        <option value="none">None</option>
                        <option value="fade-up">Fade Up</option>
                        <option value="fade-down">Fade Down</option>
                        <option value="slide-left">Slide Left</option>
                        <option value="bounce">Bounce</option>
                    </select>
                </div>

                <!-- DELETE ACTION -->
                <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold mt-2" id="btnDeleteElementFull"><i class="fa-solid fa-trash-can me-1"></i> Delete Element</button>

            </div>
        </aside>

    </div>
</div>

<!-- ADD NEW SLIDE MODAL -->
<div class="modal fade" id="modalAddSlide" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rounded-4 border-0 shadow bg-dark text-white" id="formAddSlide" enctype="multipart/form-data">
            @csrf
            <div class="modal-header border-bottom border-secondary pb-3">
                <h5 class="modal-title fw-bold text-warning" id="modalSlideTitle"><i class="fa-solid fa-plus me-2"></i>Create New Slide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="slide_id" id="modalSlideId" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Slide Section</label>
                    <select name="section_key" id="inputModalSection" class="form-select" required>
                        <option value="hero">Hero Banner</option>
                        <option value="lookbook">Lookbook</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Desktop Slide Image <span class="text-danger" id="desktopImageRequired">*</span></label>
                    <div class="mb-2 p-2 rounded-3 border border-secondary text-center bg-black">
                        <img id="modalPreviewDesktop" src="" alt="Desktop Image Preview" class="img-fluid rounded" style="max-height:120px; object-fit:contain;" hidden>
                        <span id="modalPreviewDesktopText" class="small text-muted"><i class="fa-regular fa-image me-1"></i> No Desktop Image Selected</span>
                    </div>
                    <input type="file" name="image" id="inputModalDesktopImage" class="form-control form-control-sm" accept="image/*">
                    <small class="d-block text-light opacity-75 mt-1">Hero: 1500 × 1000 px (3:2 landscape) recommended. Lookbook: 1200 × 800 px. JPG, PNG or WebP.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-warning"><i class="fa-solid fa-mobile-screen me-1"></i> Mobile View Image <span class="text-light opacity-75 fw-normal">(Optional)</span></label>
                    <div class="mb-2 p-2 rounded-3 border border-secondary text-center bg-black">
                        <img id="modalPreviewMobile" src="" alt="Mobile Image Preview" class="img-fluid rounded" style="max-height:120px; object-fit:contain;" hidden>
                        <span id="modalPreviewMobileText" class="small text-muted"><i class="fa-solid fa-mobile-screen me-1"></i> No Separate Mobile Image (Falls back to Desktop)</span>
                    </div>
                    <input type="file" name="mobile_image" id="inputModalMobileImage" class="form-control form-control-sm" accept="image/*">
                    <small class="d-block text-light opacity-75 mt-1">Recommended: 1080 × 1080 px (Square) or 1080 × 1350 px (Portrait) for mobile screen view.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Slide Title</label>
                    <input type="text" name="heading" id="inputModalHeading" class="form-control" placeholder="New Collection">
                </div>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger rounded-pill fw-bold px-4">Create Slide</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    // Current Slide & Carousel Data
    const currentSlideId = {{ $slide?->id ?? 'null' }};
    const currentSectionKey = "{{ $slide?->section_key ?? $builderSection }}";
    const initialOverlayItems = @json($slide?->overlay_items ?? []);
    const carouselPreviewSlides = @json($slides->where('section_key', $builderSection)->values()->map(fn($item) => ['image' => $item->image_url, 'heading' => $item->heading])->all());
    
    // Canvas & Viewport References
    const canvasFrame = document.getElementById('canvasFrame');
    const canvasElementsLayer = document.getElementById('canvasElementsLayer');
    const canvasBgImage = document.getElementById('canvasBgImage');
    const inspectorContent = document.getElementById('inspectorContent');
    const mobileGuide = document.getElementById('mobileGuide');
    
    // Editor State Stack
    let elements = [];
    let selectedElementId = null;
    let activeViewport = 'desktop';
    let hasMobileImage = @json((bool) ($slide?->has_mobile_image));
    let currentSlideDesktopUrl = @json($slide?->image_url ?: '');
    let currentSlideMobileUrl = @json($slide?->has_mobile_image ? $slide->mobile_image_url : '');
    let historyStack = [];
    let historyIndex = -1;
    let zoomScale = 1;
    let defaultPreviewTimer = null;
    const initialPreviewSlideIndex = Math.max(0, carouselPreviewSlides.findIndex(item => item.image === @json($slide?->image_url)));
    let previewSlideIndex = initialPreviewSlideIndex;

    function showPreviewSlide(index) {
        if (!carouselPreviewSlides.length) return;
        previewSlideIndex = (index + carouselPreviewSlides.length) % carouselPreviewSlides.length;
        const item = carouselPreviewSlides[previewSlideIndex];
        canvasBgImage.style.opacity = '0';
        const previewTitle = document.getElementById('canvasPreviewTitle');
        previewTitle.classList.remove('visible');
        window.setTimeout(() => {
            canvasBgImage.src = item.image || '';
            canvasBgImage.hidden = !item.image;
            previewTitle.textContent = item.heading || '';
            canvasBgImage.style.opacity = '';
            requestAnimationFrame(() => previewTitle.classList.toggle('visible', !!item.heading));
        }, 180);
        document.querySelectorAll('.qw-canvas-dots span').forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === previewSlideIndex));
    }

    function stopDefaultPreview() {
        window.clearInterval(defaultPreviewTimer);
        defaultPreviewTimer = null;
        canvasFrame.classList.remove('is-default-preview');
        document.getElementById('canvasPreviewTitle').classList.remove('visible');
        document.getElementById('btnDefaultPreview').innerHTML = '<i class="fa-solid fa-play me-1"></i> Default';
        canvasBgImage.src = @json($slide?->image_url ?: '');
        canvasBgImage.hidden = !@json((bool) $slide?->image_url);
        document.querySelectorAll('.qw-canvas-dots span').forEach((dot, index) => dot.classList.toggle('active', index === initialPreviewSlideIndex));
    }

    document.getElementById('btnDefaultPreview')?.addEventListener('click', () => {
        if (defaultPreviewTimer) { stopDefaultPreview(); return; }
        if (carouselPreviewSlides.length < 2) { alert('Add at least two slides to preview the carousel.'); return; }
        canvasFrame.classList.add('is-default-preview');
        document.getElementById('btnDefaultPreview').innerHTML = '<i class="fa-solid fa-stop me-1"></i> Stop Preview';
        showPreviewSlide(previewSlideIndex);
        defaultPreviewTimer = window.setInterval(() => showPreviewSlide(previewSlideIndex + 1), {{ (int) ($settings->interval_ms ?? 4000) }});
    });
    document.querySelector('.qw-canvas-arrow.left-arrow')?.addEventListener('click', () => { if (defaultPreviewTimer) showPreviewSlide(previewSlideIndex - 1); });
    document.querySelector('.qw-canvas-arrow.right-arrow')?.addEventListener('click', () => { if (defaultPreviewTimer) showPreviewSlide(previewSlideIndex + 1); });
    document.querySelectorAll('#canvasPreviewDots span').forEach(dot => dot.addEventListener('click', () => {
        if (defaultPreviewTimer) showPreviewSlide(Number(dot.dataset.previewIndex));
    }));

    // Load initial slide overlay items or default template
    function initCanvas() {
        elements = Array.isArray(initialOverlayItems) ? JSON.parse(JSON.stringify(initialOverlayItems)) : [];
        elements.forEach(ensureResponsiveConfig);
        pushHistory();
        renderCanvas();
    }

    function ensureResponsiveConfig(item) {
        item.responsive = item.responsive && typeof item.responsive === 'object' ? item.responsive : {};
        const base = { x: item.x ?? 20, y: item.y ?? 40, width: item.width ?? 0, height: item.height ?? 0, fontSize: item.size ?? 20 };
        ['desktop', 'mobile'].forEach(view => {
            const saved = item.responsive[view] && typeof item.responsive[view] === 'object' ? item.responsive[view] : {};
            item.responsive[view] = { ...base, ...saved };
        });
        return item.responsive;
    }

    function viewportConfig(item, view = activeViewport) {
        return ensureResponsiveConfig(item)[view];
    }

    // Push State to Undo History Stack
    function pushHistory() {
        historyStack = historyStack.slice(0, historyIndex + 1);
        historyStack.push(JSON.stringify(elements));
        historyIndex = historyStack.length - 1;
        updateUndoRedoButtons();
    }

    function updateUndoRedoButtons() {
        document.getElementById('btnUndo').disabled = historyIndex <= 0;
        document.getElementById('btnRedo').disabled = historyIndex >= historyStack.length - 1;
    }

    document.getElementById('btnUndo')?.addEventListener('click', () => {
        if (historyIndex > 0) {
            historyIndex--;
            elements = JSON.parse(historyStack[historyIndex]);
            renderCanvas();
            updateUndoRedoButtons();
        }
    });

    document.getElementById('btnRedo')?.addEventListener('click', () => {
        if (historyIndex < historyStack.length - 1) {
            historyIndex++;
            elements = JSON.parse(historyStack[historyIndex]);
            renderCanvas();
            updateUndoRedoButtons();
        }
    });

    // Render All Elements onto HTML Canvas
    function renderCanvas() {
        canvasElementsLayer.innerHTML = '';
        const isMobileMode = canvasFrame.classList.contains('mobile-mode');
        activeViewport = isMobileMode ? 'mobile' : 'desktop';

        const notice = document.getElementById('noMobileNotice');
        if (activeViewport === 'mobile') {
            if (hasMobileImage && currentSlideMobileUrl) {
                canvasBgImage.src = currentSlideMobileUrl;
                canvasBgImage.hidden = false;
                if (notice) notice.classList.add('d-none');
            } else {
                canvasBgImage.src = currentSlideDesktopUrl;
                canvasBgImage.hidden = !currentSlideDesktopUrl;
                if (notice) notice.classList.remove('d-none');
            }
        } else {
            if (notice) notice.classList.add('d-none');
            if (currentSlideDesktopUrl) {
                canvasBgImage.src = currentSlideDesktopUrl;
                canvasBgImage.hidden = false;
            }
        }

        elements.forEach(item => {
            const view = viewportConfig(item);
            const elNode = document.createElement('div');
            elNode.className = `qw-element-item ${selectedElementId === item.id ? 'selected' : ''}`;
            elNode.id = `node_${item.id}`;
            elNode.style.left = `${view.x}%`;
            elNode.style.top = `${view.y}%`;
            elNode.style.fontFamily = item.font || 'Inter';
            
            const computedSize = Math.max(10, Math.round(view.fontSize || item.size || 20));
            elNode.style.fontSize = `${computedSize}px`;
            elNode.style.fontWeight = item.weight || '400';
            elNode.style.color = item.color || '#000000';
            elNode.style.backgroundColor = item.bg || 'transparent';
            elNode.style.textAlign = item.align || 'left';
            elNode.style.fontStyle = item.italic ? 'italic' : 'normal';
            elNode.style.textDecoration = item.underline ? 'underline' : 'none';
            elNode.style.textTransform = item.uppercase ? 'uppercase' : 'none';
            elNode.style.borderRadius = `${item.radius || 4}px`;
            elNode.style.width = Number(view.width) > 0 ? `${Number(view.width)}px` : 'max-content';
            elNode.style.height = Number(view.height) > 0 ? `${Number(view.height)}px` : 'auto';
            elNode.style.boxSizing = 'border-box';
            
            const padVal = item.padding || 4;
            elNode.style.padding = `${padVal}px ${padVal * 1.5}px`;
            elNode.textContent = item.text;

            // Selection Event
            elNode.addEventListener('pointerdown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectElement(item.id);
                // Re-render to create resize handles when a previously unselected
                // saved element is selected from the canvas.
                renderCanvas();
                startDrag(e, item);
            });

            if (selectedElementId === item.id) {
                ['nw', 'n', 'ne', 'se', 's', 'sw'].forEach(direction => {
                    const handle = document.createElement('button');
                    handle.type = 'button';
                    handle.className = 'qw-resize-handle';
                    handle.dataset.resize = direction;
                    handle.setAttribute('aria-label', `Resize ${direction}`);
                    handle.addEventListener('pointerdown', event => {
                        event.preventDefault();
                        event.stopPropagation();
                        startResize(event, item, direction, elNode);
                    });
                    elNode.appendChild(handle);
                });
            }

            canvasElementsLayer.appendChild(elNode);
        });

        renderLayersList();
        syncInspector();
    }

    // Select Element & Populate Right Properties Inspector
    function selectElement(id) {
        selectedElementId = id;
        document.querySelectorAll('.qw-element-item').forEach(el => el.classList.remove('selected'));
        const activeNode = document.getElementById(`node_${id}`);
        if (activeNode) activeNode.classList.add('selected');
        syncInspector();
    }

    // Sync Properties Inspector Panel
    function syncInspector() {
        const item = elements.find(el => el.id === selectedElementId);
        if (!item) return;

        document.getElementById('propTextContent').value = item.text || '';
        document.getElementById('propFontFamily').value = item.font || 'Inter';
        document.getElementById('propTextAlign').value = ['left', 'center', 'right'].includes(item.align) ? item.align : 'left';
        const view = viewportConfig(item);
        document.getElementById('propFontSize').value = view.fontSize || item.size || 24;
        document.getElementById('propElementWidth').value = Number(view.width) > 0 ? view.width : '';
        document.getElementById('propElementHeight').value = Number(view.height) > 0 ? view.height : '';
        document.getElementById('propFontWeight').value = item.weight || '400';
        document.getElementById('propTextColor').value = item.color || '#6B1E3F';
        document.getElementById('propTextColorHex').value = (item.color || '#6B1E3F').toUpperCase();
        document.getElementById('propBgColor').value = item.bg !== 'transparent' ? item.bg : '#f0c75e';
        document.getElementById('propBgColorHex').value = (item.bg !== 'transparent' ? item.bg : '#F0C75E').toUpperCase();
        
        document.getElementById('propHasLink').checked = !!item.linkHas;
        document.getElementById('linkFieldsWrap').classList.toggle('d-none', !item.linkHas);
        document.getElementById('propLinkUrl').value = item.linkUrl || '';
        document.getElementById('propAnimation').value = item.animation || 'none';
    }

    // Bind Live Input Listeners to Inspector Controls
    document.getElementById('propTextContent')?.addEventListener('input', (e) => {
        updateActiveProp('text', e.target.value);
    });
    document.getElementById('propFontFamily')?.addEventListener('change', (e) => {
        updateActiveProp('font', e.target.value);
    });
    document.getElementById('propTextAlign')?.addEventListener('change', (e) => {
        updateActiveProp('align', e.target.value);
    });
    document.getElementById('propFontSize')?.addEventListener('input', (e) => {
        updateActiveProp('size', parseInt(e.target.value) || 20);
    });
    document.getElementById('propElementWidth')?.addEventListener('input', (e) => {
        updateActiveProp('width', Math.max(0, Math.min(1080, parseInt(e.target.value, 10) || 0)));
    });
    document.getElementById('propElementHeight')?.addEventListener('input', (e) => {
        updateActiveProp('height', Math.max(0, Math.min(450, parseInt(e.target.value, 10) || 0)));
    });
    document.getElementById('propFontWeight')?.addEventListener('change', (e) => {
        updateActiveProp('weight', e.target.value);
    });
    document.getElementById('propTextColor')?.addEventListener('input', (e) => {
        updateActiveProp('color', e.target.value);
        document.getElementById('propTextColorHex').value = e.target.value.toUpperCase();
    });
    document.getElementById('propBgColor')?.addEventListener('input', (e) => {
        updateActiveProp('bg', e.target.value);
        document.getElementById('propBgColorHex').value = e.target.value.toUpperCase();
    });
    document.getElementById('propHasLink')?.addEventListener('change', (e) => {
        updateActiveProp('linkHas', e.target.checked);
        document.getElementById('linkFieldsWrap').classList.toggle('d-none', !e.target.checked);
    });
    document.getElementById('propLinkUrl')?.addEventListener('input', (e) => {
        updateActiveProp('linkUrl', e.target.value);
    });
    document.getElementById('propAnimation')?.addEventListener('change', (e) => {
        updateActiveProp('animation', e.target.value);
    });

    function updateActiveProp(key, value) {
        if (!selectedElementId) return;
        const item = elements.find(el => el.id === selectedElementId);
        if (item) {
            const responsiveKey = key === 'size' ? 'fontSize' : key;
            if (['x', 'y', 'width', 'height', 'size'].includes(key)) viewportConfig(item)[responsiveKey] = value;
            else item[key] = value;
            renderCanvas();
            pushHistory();
        }
    }

    // Drag Element Logic on Canvas
    function startDrag(e, item) {
        let isDragging = true;
        const frameRect = canvasFrame.getBoundingClientRect();
        const startX = e.clientX;
        const startY = e.clientY;
        const view = viewportConfig(item);
        const origX = view.x;
        const origY = view.y;

        function onPointerMove(moveEvent) {
            if (!isDragging) return;
            const deltaX = moveEvent.clientX - startX;
            const deltaY = moveEvent.clientY - startY;

            let newX = origX + (deltaX / frameRect.width) * 100;
            let newY = origY + (deltaY / frameRect.height) * 100;

            const node = document.getElementById(`node_${item.id}`);
            const maxX = Math.max(0, 100 - ((node?.offsetWidth || 0) / canvasFrame.clientWidth) * 100);
            const maxY = Math.max(0, 100 - ((node?.offsetHeight || 0) / canvasFrame.clientHeight) * 100);
            view.x = Math.max(0, Math.min(maxX, newX));
            view.y = Math.max(0, Math.min(maxY, newY));

            const activeNode = document.getElementById(`node_${item.id}`);
            if (activeNode) {
                activeNode.style.left = `${view.x}%`;
                activeNode.style.top = `${view.y}%`;
            }
        }

        function onPointerUp() {
            if (isDragging) {
                isDragging = false;
                window.removeEventListener('pointermove', onPointerMove);
                window.removeEventListener('pointerup', onPointerUp);
                pushHistory();
            }
        }

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp);
    }

    function startResize(e, item, direction, node) {
        const frameRect = canvasFrame.getBoundingClientRect();
        const scaleX = frameRect.width / canvasFrame.clientWidth || 1;
        const scaleY = frameRect.height / canvasFrame.clientHeight || 1;
        const nodeRect = node.getBoundingClientRect();
        const view = viewportConfig(item);
        const startX = e.clientX;
        const startY = e.clientY;
        const startWidth = nodeRect.width / scaleX;
        const startHeight = nodeRect.height / scaleY;
        const startLeft = view.x;
        const startTop = view.y;
        view.width = Math.round(startWidth);
        view.height = Math.round(startHeight);

        function onPointerMove(moveEvent) {
            const dx = (moveEvent.clientX - startX) / scaleX;
            const dy = (moveEvent.clientY - startY) / scaleY;
            let width = startWidth;
            let height = startHeight;
            let x = startLeft;
            let y = startTop;

            if (direction.includes('e')) width += dx;
            if (direction.includes('w')) { width -= dx; x += (dx / canvasFrame.clientWidth) * 100; }
            if (direction.includes('s')) height += dy;
            if (direction.includes('n')) { height -= dy; y += (dy / canvasFrame.clientHeight) * 100; }

            view.width = Math.max(12, Math.min(canvasFrame.clientWidth, Math.round(width)));
            view.height = Math.max(12, Math.min(canvasFrame.clientHeight, Math.round(height)));
            view.x = Math.max(0, Math.min(100 - (view.width / canvasFrame.clientWidth) * 100, x));
            view.y = Math.max(0, Math.min(100 - (view.height / canvasFrame.clientHeight) * 100, y));
            node.style.width = `${view.width}px`;
            node.style.height = `${view.height}px`;
            node.style.left = `${view.x}%`;
            node.style.top = `${view.y}%`;
            document.getElementById('propElementWidth').value = view.width;
            document.getElementById('propElementHeight').value = view.height;
        }

        function onPointerUp() {
            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            pushHistory();
        }

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp);
    }

    // HTML5 Drag and Drop from Left Drawer onto Canvas
    canvasFrame.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });

    canvasFrame.addEventListener('drop', (e) => {
        e.preventDefault();
        const rawData = e.dataTransfer.getData('application/json');
        if (!rawData) return;
        try {
            const itemData = JSON.parse(rawData);
            const rect = canvasFrame.getBoundingClientRect();
            const dropX = ((e.clientX - rect.left) / rect.width) * 100;
            const dropY = ((e.clientY - rect.top) / rect.height) * 100;
            itemData.x = Math.max(2, Math.min(85, Math.round(dropX)));
            itemData.y = Math.max(2, Math.min(85, Math.round(dropY)));
            addElement(itemData);
        } catch(err) {
            console.error("Drop parsing error:", err);
        }
    });

    // Make Sidebar Element Tiles Draggable
    function makeElementDraggable(selector, getPayloadFn) {
        document.querySelectorAll(selector).forEach(el => {
            el.setAttribute('draggable', 'true');
            el.addEventListener('dragstart', (e) => {
                const payload = typeof getPayloadFn === 'function' ? getPayloadFn(el) : getPayloadFn;
                e.dataTransfer.setData('application/json', JSON.stringify(payload));
                e.dataTransfer.effectAllowed = 'copy';
            });
        });
    }

    // Bind Draggables for Tiles & Presets
    makeElementDraggable('#addTileText', { type: 'text', text: 'Sample Text', font: 'Inter', size: 28, weight: '600', color: '#111111' });
    makeElementDraggable('#addTileImage', { type: 'image', text: '📷 Image Frame', font: 'Inter', size: 16, weight: '600', color: '#ffffff', bg: '#333333', padding: 12, radius: 8 });
    makeElementDraggable('#addTileButton', { type: 'button', text: 'SHOP NOW →', font: 'Inter', size: 14, weight: '800', color: '#ffffff', bg: '#6B1E3F', radius: 30, padding: 8 });
    makeElementDraggable('#addTileShape', { type: 'shape', text: ' ', font: 'Inter', size: 40, weight: '400', color: '#ffffff', bg: '#f0c75e', radius: 8, padding: 15 });
    makeElementDraggable('#addTileIcon', { type: 'text', text: '★', font: 'Inter', size: 36, weight: '800', color: '#f0c75e' });
    makeElementDraggable('#addTileBadge', { type: 'badge', text: 'NEW ARRIVAL', font: 'Inter', size: 12, weight: '800', color: '#ffffff', bg: '#ff6b8b', radius: 20, padding: 4 });

    makeElementDraggable('#btnAddHeading', { type: 'text', text: 'New Arrivals', font: 'Playfair Display', size: 48, weight: '700', color: '#6B1E3F' });
    makeElementDraggable('#btnAddSubheading', { type: 'text', text: 'Trendy Styles for Every You', font: 'Lora', size: 22, weight: '400', color: '#21180a', italic: true });
    makeElementDraggable('#btnAddParagraph', { type: 'text', text: 'Discover the latest runway collection.', font: 'Inter', size: 16, weight: '400', color: '#4a4e57' });
    makeElementDraggable('#btnAddSmallText', { type: 'text', text: 'Limited period offer.', font: 'Inter', size: 12, weight: '500', color: '#888888' });

    makeElementDraggable('[data-preset]', (btn) => {
        const preset = btn.dataset.preset;
        if (preset === 'new_pink') return { type: 'badge', text: 'NEW', font: 'Inter', size: 13, weight: '800', color: '#ffffff', bg: '#ff6b8b', radius: 20, padding: 5 };
        if (preset === 'sale_red') return { type: 'badge', text: 'SALE 50% OFF', font: 'Inter', size: 15, weight: '900', color: '#ffffff', bg: '#d92338', radius: 4, padding: 7 };
        if (preset === 'bestseller_gold') return { type: 'badge', text: '★ BEST SELLER', font: 'Montserrat', size: 13, weight: '800', color: '#21180a', bg: '#f0c75e', radius: 30, padding: 5 };
        if (preset === 'shop_now_btn') return { type: 'button', text: 'SHOP NOW →', font: 'Inter', size: 15, weight: '800', color: '#ffffff', bg: '#6B1E3F', radius: 30, padding: 10, linkHas: true, linkUrl: '/shop' };
        if (preset === 'free_delivery') return { type: 'badge', text: '🚚 ₹300+ FREE Delivery', font: 'Inter', size: 12, weight: '700', color: '#ffffff', bg: '#10b981', radius: 20, padding: 5 };
        return { type: 'text', text: 'Preset Element', font: 'Inter', size: 18, weight: '600', color: '#ffffff' };
    });

    // Add New Elements via Left Sidebar Buttons (Clicks)
    document.getElementById('addTileText')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Sample Text', x: 20, y: 40, font: 'Inter', size: 28, weight: '600', color: '#ffffff' });
    });
    document.getElementById('addTileImage')?.addEventListener('click', () => {
        document.getElementById('inputSlideImage')?.click();
    });
    document.getElementById('addTileButton')?.addEventListener('click', () => {
        addElement({ type: 'button', text: 'SHOP NOW →', x: 20, y: 60, font: 'Inter', size: 14, weight: '800', color: '#ffffff', bg: '#6B1E3F', radius: 30, padding: 8 });
    });
    document.getElementById('addTileShape')?.addEventListener('click', () => {
        addElement({ type: 'shape', text: ' ', x: 20, y: 40, font: 'Inter', size: 40, weight: '400', color: '#ffffff', bg: '#f0c75e', radius: 8, padding: 15 });
    });
    document.getElementById('addTileIcon')?.addEventListener('click', () => {
        addElement({ type: 'text', text: '★', x: 20, y: 40, font: 'Inter', size: 36, weight: '800', color: '#f0c75e' });
    });
    document.getElementById('addTileBadge')?.addEventListener('click', () => {
        addElement({ type: 'badge', text: 'NEW ARRIVAL', x: 20, y: 25, font: 'Inter', size: 12, weight: '800', color: '#ffffff', bg: '#ff6b8b', radius: 20, padding: 4 });
    });

    document.getElementById('btnAddHeading')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'New Arrivals', x: 15, y: 35, font: 'Playfair Display', size: 54, weight: '700', color: '#6B1E3F', animation: 'fade-up' });
    });
    document.getElementById('btnAddSubheading')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Trendy Styles for Every You', x: 15, y: 55, font: 'Lora', size: 24, weight: '400', color: '#21180a', italic: true, animation: 'fade-up' });
    });
    document.getElementById('btnAddParagraph')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Discover the latest runway collection.', x: 15, y: 65, font: 'Inter', size: 16, weight: '400', color: '#4a4e57', animation: 'fade' });
    });
    document.getElementById('btnAddSmallText')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Limited period offer.', x: 15, y: 80, font: 'Inter', size: 12, weight: '500', color: '#888888' });
    });

    // Style Presets for Text
    document.getElementById('btnPresetLuxury')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Elegance & Luxury', x: 15, y: 35, font: 'Playfair Display', size: 44, weight: '700', color: '#D4AF37' });
    });
    document.getElementById('btnPresetBoldPromo')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'SPECIAL OFFER - 50% OFF', x: 15, y: 35, font: 'Montserrat', size: 36, weight: '900', color: '#ff4757' });
    });
    document.getElementById('btnPresetItalicSubtitle')?.addEventListener('click', () => {
        addElement({ type: 'text', text: 'Handcrafted with fine detail', x: 15, y: 55, font: 'Lora', size: 22, weight: '400', italic: true, color: '#f1f2f6' });
    });

    // Button Presets Tab
    document.querySelectorAll('[data-btn-style]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = btn.dataset.btnStyle;
            if (s === 'maroon') addElement({ type: 'button', text: 'SHOP NOW →', x: 20, y: 65, font: 'Inter', size: 14, weight: '800', color: '#ffffff', bg: '#6B1E3F', radius: 30, padding: 8, linkHas: true, linkUrl: '/shop' });
            if (s === 'gold') addElement({ type: 'button', text: 'EXPLORE COLLECTION', x: 20, y: 65, font: 'Inter', size: 14, weight: '800', color: '#21180a', bg: '#f0c75e', radius: 30, padding: 8, linkHas: true, linkUrl: '/shop' });
            if (s === 'outline') addElement({ type: 'button', text: 'DISCOVER MORE', x: 20, y: 65, font: 'Inter', size: 14, weight: '800', color: '#ffffff', bg: 'transparent', radius: 30, padding: 8, linkHas: true, linkUrl: '/shop' });
        });
    });

    // Shape Presets Tab
    document.querySelectorAll('[data-shape]').forEach(btn => {
        btn.addEventListener('click', () => {
            const sh = btn.dataset.shape;
            if (sh === 'rect') addElement({ type: 'shape', text: ' ', x: 20, y: 35, font: 'Inter', size: 30, weight: '400', color: '#ffffff', bg: '#1f2228', radius: 0, padding: 20 });
            if (sh === 'rounded') addElement({ type: 'shape', text: ' ', x: 20, y: 35, font: 'Inter', size: 30, weight: '400', color: '#ffffff', bg: '#1f2228', radius: 12, padding: 20 });
            if (sh === 'circle') addElement({ type: 'shape', text: ' ', x: 20, y: 35, font: 'Inter', size: 30, weight: '400', color: '#ffffff', bg: '#f0c75e', radius: 50, padding: 20 });
        });
    });

    // Icon Tiles Tab
    document.querySelectorAll('[data-icon]').forEach(btn => {
        btn.addEventListener('click', () => {
            const ic = btn.dataset.icon;
            const iconMap = { 'fa-heart': '❤️', 'fa-truck': '🚚', 'fa-star': '★', 'fa-bag-shopping': '🛍️' };
            addElement({ type: 'text', text: iconMap[ic] || '★', x: 25, y: 40, font: 'Inter', size: 36, weight: '800', color: '#f0c75e' });
        });
    });

    // Badges Tab
    document.getElementById('btnAddBadgeSale')?.addEventListener('click', () => {
        addElement({ type: 'badge', text: 'SALE - 50% OFF', x: 20, y: 20, font: 'Inter', size: 14, weight: '900', color: '#ffffff', bg: '#d92338', radius: 20, padding: 6 });
    });
    document.getElementById('btnAddBadgeBestseller')?.addEventListener('click', () => {
        addElement({ type: 'badge', text: '★ BEST SELLER', x: 20, y: 20, font: 'Montserrat', size: 13, weight: '800', color: '#21180a', bg: '#f0c75e', radius: 30, padding: 5 });
    });

    // Slide Background Image File Selector
    document.getElementById('inputSlideImage')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                currentSlideDesktopUrl = e.target.result;
                const tabImg = document.getElementById('tabPreviewDesktop');
                const tabTxt = document.getElementById('tabPreviewDesktopText');
                if (tabImg) { tabImg.src = currentSlideDesktopUrl; tabImg.hidden = false; }
                if (tabTxt) { tabTxt.classList.add('d-none'); }
                renderCanvas();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('inputMobileSlideImage')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                currentSlideMobileUrl = e.target.result;
                hasMobileImage = true;
                const tabImg = document.getElementById('tabPreviewMobile');
                const tabTxt = document.getElementById('tabPreviewMobileText');
                if (tabImg) { tabImg.src = currentSlideMobileUrl; tabImg.hidden = false; }
                if (tabTxt) { tabTxt.classList.add('d-none'); }
                renderCanvas();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Modal Live Image Previews
    document.getElementById('inputModalDesktopImage')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const imgDesktop = document.getElementById('modalPreviewDesktop');
                const txtDesktop = document.getElementById('modalPreviewDesktopText');
                if (imgDesktop) { imgDesktop.src = e.target.result; imgDesktop.hidden = false; }
                if (txtDesktop) { txtDesktop.classList.add('d-none'); }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('inputModalMobileImage')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const imgMobile = document.getElementById('modalPreviewMobile');
                const txtMobile = document.getElementById('modalPreviewMobileText');
                if (imgMobile) { imgMobile.src = e.target.result; imgMobile.hidden = false; }
                if (txtMobile) { txtMobile.classList.add('d-none'); }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Custom Upload Image Overlay
    document.getElementById('inputCustomUpload')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                addElement({ type: 'image', text: '📷 Custom Image', bgImage: e.target.result, x: 25, y: 25, font: 'Inter', size: 16, weight: '600', color: '#ffffff', bg: 'transparent' });
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Canvas Background Color Picker
    document.getElementById('canvasBgColor')?.addEventListener('input', (e) => {
        canvasFrame.style.backgroundColor = e.target.value;
    });

    // Preset Badges
    document.querySelectorAll('[data-preset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const preset = btn.dataset.preset;
            if (preset === 'new_pink') addElement({ type: 'badge', text: 'NEW', x: 15, y: 20, font: 'Inter', size: 13, weight: '800', color: '#ffffff', bg: '#ff6b8b', radius: 20, padding: 5 });
            if (preset === 'sale_red') addElement({ type: 'badge', text: 'SALE 50% OFF', x: 15, y: 20, font: 'Inter', size: 15, weight: '900', color: '#ffffff', bg: '#d92338', radius: 4, padding: 7 });
            if (preset === 'bestseller_gold') addElement({ type: 'badge', text: '★ BEST SELLER', x: 15, y: 20, font: 'Montserrat', size: 13, weight: '800', color: '#21180a', bg: '#f0c75e', radius: 30, padding: 5 });
            if (preset === 'shop_now_btn') addElement({ type: 'button', text: 'SHOP NOW →', x: 15, y: 70, font: 'Inter', size: 15, weight: '800', color: '#ffffff', bg: '#6B1E3F', radius: 30, padding: 10, linkHas: true, linkUrl: '/shop' });
            if (preset === 'free_delivery') addElement({ type: 'badge', text: '🚚 ₹300+ FREE Delivery', x: 15, y: 80, font: 'Inter', size: 12, weight: '700', color: '#ffffff', bg: '#10b981', radius: 20, padding: 5 });
        });
    });

    function addElement(data) {
        const newEl = Object.assign({ id: 'el_' + Date.now(), x: 20, y: 40, font: 'Inter', size: 20, weight: '400', color: '#000000', bg: 'transparent' }, data);
        elements.push(newEl);
        selectElement(newEl.id);
        renderCanvas();
        pushHistory();
    }

    // Delete Element
    document.getElementById('btnDeleteSelectedElement')?.addEventListener('click', deleteActiveElement);
    document.getElementById('btnDeleteElementFull')?.addEventListener('click', deleteActiveElement);

    function deleteActiveElement() {
        if (!selectedElementId) return;
        elements = elements.filter(el => el.id !== selectedElementId);
        selectedElementId = null;
        renderCanvas();
        pushHistory();
    }

    // Left Sidebar Navigation Tabs Toggle
    document.querySelectorAll('.qw-nav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.id === 'btnToggleSidebar') return;
            document.querySelectorAll('.qw-nav-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.qw-tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = document.getElementById(`tab-${btn.dataset.tab}`);
            if (target) target.classList.add('active');
        });
    });

    // Render Layers List
    function renderLayersList() {
        const container = document.getElementById('layerListContainer');
        if (!container) return;
        if (elements.length === 0) {
            container.innerHTML = '<p class="text-muted small text-center py-3 mb-0">No elements on canvas</p>';
            return;
        }
        container.innerHTML = elements.map((item, idx) => `
            <div class="d-flex justify-content-between align-items-center p-2 mb-1 bg-dark rounded-3 ${selectedElementId === item.id ? 'border border-primary' : ''}" style="cursor:pointer" onclick="selectLayer('${item.id}')">
                <span class="small text-light text-truncate" style="max-width:130px"><i class="fa-solid fa-layer-group me-1 text-secondary"></i> ${item.text || 'Element'}</span>
            </div>
        `).join('');
    }

    window.selectLayer = function(id) { selectElement(id); renderCanvas(); };

    // Responsive Desktop / Mobile View Toggle (Real Mobile View Mode)
    document.getElementById('btnViewDesktop')?.addEventListener('click', () => {
        document.getElementById('btnViewDesktop').classList.add('active');
        document.getElementById('btnViewMobile').classList.remove('active');
        activeViewport = 'desktop';
        canvasFrame.classList.remove('mobile-mode');
        if (mobileGuide) mobileGuide.classList.add('d-none');
        renderCanvas();
    });

    document.getElementById('btnViewMobile')?.addEventListener('click', () => {
        document.getElementById('btnViewMobile').classList.add('active');
        document.getElementById('btnViewDesktop').classList.remove('active');
        activeViewport = 'mobile';
        canvasFrame.classList.add('mobile-mode');
        if (mobileGuide) mobileGuide.classList.remove('d-none');
        renderCanvas();
    });

    // Zoom Controls
    document.getElementById('btnZoomIn')?.addEventListener('click', () => {
        if (zoomScale < 1.5) {
            zoomScale += 0.1;
            applyZoom();
        }
    });

    document.getElementById('btnZoomOut')?.addEventListener('click', () => {
        if (zoomScale > 0.6) {
            zoomScale -= 0.1;
            applyZoom();
        }
    });

    function applyZoom() {
        canvasFrame.style.transform = `scale(${zoomScale})`;
        document.getElementById('zoomLabel').textContent = `${Math.round(zoomScale * 100)}%`;
    }

    // Save Draft & Publish Buttons
    document.getElementById('heroCarouselToggle')?.addEventListener('change', async event => {
        const toggle = event.currentTarget;
        toggle.disabled = true;
        try {
            const response = await fetch("{{ route('admin.home-carousel.hero-toggle') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ enabled: toggle.checked })
            });
            if (!response.ok) throw new Error('Could not update hero carousel.');
        } catch (error) {
            toggle.checked = !toggle.checked;
            alert(error.message);
        } finally { toggle.disabled = false; }
    });

    document.getElementById('btnGenerateSample')?.addEventListener('click', () => {
        const stamp = Date.now();
        elements = [
            { id: `el_${stamp}_1`, type: 'text', text: 'New Arrivals', x: 12, y: 32, font: 'Playfair Display', size: 56, weight: '700', color: '#6B1E3F', bg: 'transparent', align: 'left', linkHas: false, linkUrl: '' },
            { id: `el_${stamp}_2`, type: 'text', text: 'Trendy Styles for Every You', x: 12, y: 52, font: 'Lora', size: 24, weight: '400', color: '#21180a', bg: 'transparent', align: 'left', italic: true },
            { id: `el_${stamp}_3`, type: 'button', text: 'SHOP NOW →', x: 12, y: 70, font: 'Inter', size: 15, weight: '800', color: '#ffffff', bg: '#6B1E3F', align: 'center', linkHas: true, linkUrl: '/shop', radius: 30, padding: 10 }
        ];
        pushHistory();
        renderCanvas();
    });

    document.getElementById('btnPublish')?.addEventListener('click', () => saveSlide('active'));
    document.getElementById('btnSaveDraft')?.addEventListener('click', () => saveSlide('inactive'));

    function saveSlide(status) {
        if (!currentSlideId) {
            alert('Please select or create a slide first.');
            return;
        }

        const formData = new FormData();
        formData.append('slide_id', currentSlideId);
        formData.append('section_key', currentSectionKey);
        formData.append('status', status);
        formData.append('overlay_items', JSON.stringify(elements));

        const desktopInput = document.getElementById('inputSlideImage');
        if (desktopInput && desktopInput.files[0]) {
            formData.append('image', desktopInput.files[0]);
        }

        const mobileInput = document.getElementById('inputMobileSlideImage');
        if (mobileInput && mobileInput.files[0]) {
            formData.append('mobile_image', mobileInput.files[0]);
        }

        fetch("{{ route('admin.home-carousel.builder.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.slide) {
                    if (data.slide.image_url) currentSlideDesktopUrl = data.slide.image_url;
                    if (data.slide.mobile_image_url) currentSlideMobileUrl = data.slide.mobile_image_url;
                }
                alert(status === 'active' ? 'Slide Published Successfully!' : 'Slide Draft Saved Successfully!');
            } else {
                alert('Error saving slide: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to save slide.');
        });
    }

    // Slide Thumbnails Switching
    document.querySelectorAll('.qw-slide-thumb-card').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.qw-thumb-delete') || e.target.closest('.qw-thumb-edit')) return;
            window.location.href = card.dataset.slideUrl;
        });

        card.querySelector('.qw-thumb-edit')?.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            const btn = e.currentTarget;
            const slideId = btn.dataset.slideId;
            const heading = btn.dataset.heading || '';
            const desktopUrl = btn.dataset.desktopUrl || '';
            const mobileUrl = btn.dataset.mobileUrl || '';
            const hasMobile = btn.dataset.hasMobile === '1';

            document.getElementById('modalSlideTitle').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Edit Slide Details & Images';
            document.getElementById('modalSlideId').value = slideId;
            document.getElementById('inputModalHeading').value = heading;

            const imgDesktop = document.getElementById('modalPreviewDesktop');
            const txtDesktop = document.getElementById('modalPreviewDesktopText');
            if (desktopUrl) {
                imgDesktop.src = desktopUrl;
                imgDesktop.hidden = false;
                txtDesktop.classList.add('d-none');
            } else {
                imgDesktop.hidden = true;
                txtDesktop.classList.remove('d-none');
            }

            const imgMobile = document.getElementById('modalPreviewMobile');
            const txtMobile = document.getElementById('modalPreviewMobileText');
            if (hasMobile && mobileUrl) {
                imgMobile.src = mobileUrl;
                imgMobile.hidden = false;
                txtMobile.classList.add('d-none');
            } else {
                imgMobile.hidden = true;
                txtMobile.classList.remove('d-none');
                txtMobile.textContent = 'No Separate Mobile Image (Falls back to Desktop)';
            }

            const reqSpan = document.getElementById('desktopImageRequired');
            if (reqSpan) reqSpan.classList.add('d-none');
            const fileDesktop = document.getElementById('inputModalDesktopImage');
            if (fileDesktop) fileDesktop.required = false;

            const modal = new bootstrap.Modal(document.getElementById('modalAddSlide'));
            modal.show();
        });

        card.querySelector('.qw-thumb-delete')?.addEventListener('click', async event => {
            event.stopPropagation();
            if (!confirm('Delete this slide? This cannot be undone.')) return;
            const button = event.currentTarget;
            button.disabled = true;
            try {
                const response = await fetch(button.dataset.deleteUrl, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Could not delete this slide.');
                window.location.href = "{{ route('admin.home-carousel.builder', ['section' => $builderSection]) }}";
            } catch (error) {
                button.disabled = false;
                alert(error.message);
            }
        });

        card.addEventListener('dragstart', event => {
            if (event.target.closest('.qw-thumb-delete') || event.target.closest('.qw-thumb-edit')) { event.preventDefault(); return; }
            card.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.slideId);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('is-dragging');
            document.querySelectorAll('.qw-slide-thumb-card.drop-target').forEach(item => item.classList.remove('drop-target'));
        });
        card.addEventListener('dragover', event => {
            event.preventDefault();
            if (!card.classList.contains('is-dragging')) card.classList.add('drop-target');
        });
        card.addEventListener('dragleave', event => {
            if (!card.contains(event.relatedTarget)) card.classList.remove('drop-target');
        });
        card.addEventListener('drop', event => {
            event.preventDefault();
            card.classList.remove('drop-target');
            const dragged = document.querySelector('.qw-slide-thumb-card.is-dragging');
            if (!dragged || dragged === card) return;
            const bounds = card.getBoundingClientRect();
            const insertAfter = event.clientX > bounds.left + bounds.width / 2;
            card.parentElement.insertBefore(dragged, insertAfter ? card.nextSibling : card);
            saveSlideOrder();
        });
    });

    async function saveSlideOrder() {
        const slideIds = [...document.querySelectorAll('#slideThumbsList .qw-slide-thumb-card')].map(card => Number(card.dataset.slideId));
        try {
            const response = await fetch("{{ route('admin.home-carousel.slides.reorder') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ section_key: currentSectionKey, slide_ids: slideIds })
            });
            if (!response.ok) throw new Error('Could not save slide order.');
            document.querySelectorAll('#slideThumbsList .qw-slide-thumb-card').forEach((card, index) => {
                const number = card.querySelector('.qw-thumb-num');
                if (number) number.textContent = index + 1;
            });
        } catch (error) {
            alert(error.message);
            window.location.reload();
        }
    }

    // Add Slide Modal Trigger Reset
    document.getElementById('btnAddSlideModal')?.addEventListener('click', () => {
        document.getElementById('modalSlideTitle').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Create New Slide';
        document.getElementById('modalSlideId').value = '';
        document.getElementById('inputModalHeading').value = '';

        const imgDesktop = document.getElementById('modalPreviewDesktop');
        const txtDesktop = document.getElementById('modalPreviewDesktopText');
        if (imgDesktop) imgDesktop.hidden = true;
        if (txtDesktop) txtDesktop.classList.remove('d-none');

        const imgMobile = document.getElementById('modalPreviewMobile');
        const txtMobile = document.getElementById('modalPreviewMobileText');
        if (imgMobile) imgMobile.hidden = true;
        if (txtMobile) txtMobile.classList.remove('d-none');

        const reqSpan = document.getElementById('desktopImageRequired');
        if (reqSpan) reqSpan.classList.remove('d-none');
        const fileDesktop = document.getElementById('inputModalDesktopImage');
        if (fileDesktop) fileDesktop.required = true;

        const modal = new bootstrap.Modal(document.getElementById('modalAddSlide'));
        modal.show();
    });

    // Form Add Slide Submission
    document.getElementById('formAddSlide')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('status', 'active');

        fetch("{{ route('admin.home-carousel.builder.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = "{{ url('/admin/home-carousel/builder') }}/" + data.slide.id;
            } else {
                alert('Error saving slide: ' + (data.message || 'Unknown error'));
            }
        });
    });

    // Keyboard Shortcuts (Delete, Escape, Ctrl+Z)
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'Delete' || e.key === 'Backspace') deleteActiveElement();
    });

    // Sidebar Collapse / Expand Toggle
    const sidebarWrapper = document.querySelector('.qw-sidebar-wrapper');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const iconToggleSidebar = document.getElementById('iconToggleSidebar');
    const textToggleSidebar = document.getElementById('textToggleSidebar');

    btnToggleSidebar?.addEventListener('click', () => {
        const isCollapsed = sidebarWrapper.classList.toggle('collapsed');
        if (iconToggleSidebar) {
            iconToggleSidebar.className = isCollapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left';
        }
        if (textToggleSidebar) {
            textToggleSidebar.textContent = isCollapsed ? 'Expand' : 'Collapse';
        }
    });

    // Auto-expand sidebar when clicking any navigation tab if currently collapsed
    document.querySelectorAll('.qw-nav-btn:not(#btnToggleSidebar)').forEach(btn => {
        btn.addEventListener('click', () => {
            if (sidebarWrapper.classList.contains('collapsed')) {
                sidebarWrapper.classList.remove('collapsed');
                if (iconToggleSidebar) iconToggleSidebar.className = 'fa-solid fa-angles-left';
                if (textToggleSidebar) textToggleSidebar.textContent = 'Collapse';
            }
        });
    });

    // Initialize Canvas
    initCanvas();
})();
</script>
</body>
</html>
