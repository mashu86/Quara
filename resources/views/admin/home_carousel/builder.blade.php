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
            height: 250px !important;
            border-radius: 14px !important;
            box-shadow: 0 0 0 8px #1e2026, 0 25px 70px rgba(0,0,0,0.85) !important;
            border: 3px solid #3d414a !important;
        }

        .qw-canvas-frame img#canvasBgImage {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

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
        }

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
                        <label class="form-label small fw-bold">Upload Slide Image</label>
                        <input type="file" id="inputSlideImage" class="form-control form-control-sm" accept="image/*">
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
                    <img id="canvasBgImage" src="{{ $slide?->image_url ?: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1440&auto=format&fit=crop' }}" alt="Background slide">
                    
                    <!-- Decorative Canvas SVG Overlay -->
                    <div class="qw-canvas-art-layer" id="canvasArtLayer"></div>

                    <!-- Interactive Elements Container Layer -->
                    <div class="qw-canvas-elements-layer" id="canvasElementsLayer"></div>
                    
                    <!-- Navigation Arrows Preview -->
                    <div class="qw-canvas-arrow left-arrow"><i class="fa-solid fa-chevron-left"></i></div>
                    <div class="qw-canvas-arrow right-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                    
                    <!-- Pagination Dots Preview -->
                    <div class="qw-canvas-dots">
                        <span class="active"></span><span></span><span></span><span></span>
                    </div>

                    <!-- Mobile View Bounds Guide Overlay -->
                    <div class="qw-mobile-guide d-none" id="mobileGuide">
                        <div class="qw-mobile-safe-label">Mobile Safe Zone</div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM SLIDE MANAGER STRIP -->
            <div class="qw-slide-manager-strip">
                <div class="d-flex justify-content-between align-items-center me-2">
                    <span class="fw-bold small text-light"><i class="fa-solid fa-film me-1 text-warning"></i> Slides ({{ $slides->count() }})</span>
                </div>
                <div class="qw-slide-thumbs-wrapper" id="slideThumbsList">
                    @foreach($slides as $index => $item)
                        <div class="qw-slide-thumb-card {{ ($slide?->id === $item->id) ? 'active' : '' }}" data-slide-id="{{ $item->id }}" data-slide-url="{{ route('admin.home-carousel.builder', $item->id) }}">
                            <div class="qw-thumb-img-wrap">
                                <img src="{{ $item->image_url }}" alt="Slide {{ $index+1 }}">
                                <span class="qw-thumb-num">{{ $index+1 }}</span>
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
                <h5 class="modal-title fw-bold text-warning"><i class="fa-solid fa-plus me-2"></i>Create New Slide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Slide Section</label>
                    <select name="section_key" class="form-select" required>
                        <option value="hero">Hero Banner</option>
                        <option value="lookbook">Lookbook</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Slide Image <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-light">Slide Title</label>
                    <input type="text" name="heading" class="form-control" placeholder="New Collection">
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
    const currentSectionKey = "{{ $slide?->section_key ?? 'hero' }}";
    const initialOverlayItems = @json($slide?->overlay_items ?? []);
    
    // Canvas & Viewport References
    const canvasFrame = document.getElementById('canvasFrame');
    const canvasElementsLayer = document.getElementById('canvasElementsLayer');
    const canvasBgImage = document.getElementById('canvasBgImage');
    const inspectorContent = document.getElementById('inspectorContent');
    const mobileGuide = document.getElementById('mobileGuide');
    
    // Editor State Stack
    let elements = [];
    let selectedElementId = null;
    let historyStack = [];
    let historyIndex = -1;
    let zoomScale = 1;

    // Load initial slide overlay items or default template
    function initCanvas() {
        if (initialOverlayItems && initialOverlayItems.length > 0) {
            elements = JSON.parse(JSON.stringify(initialOverlayItems));
        } else {
            elements = [
                { id: 'el_1', type: 'text', text: 'New Arrivals', x: 12, y: 32, font: 'Playfair Display', size: 56, weight: '700', color: '#6B1E3F', bg: 'transparent', align: 'left', italic: false, underline: false, uppercase: false, linkHas: true, linkUrl: '/shop', animation: 'fade-up' },
                { id: 'el_2', type: 'text', text: 'Trendy Styles for Every You', x: 12, y: 52, font: 'Lora', size: 24, weight: '400', color: '#21180a', bg: 'transparent', align: 'left', italic: true, underline: false, uppercase: false, linkHas: false, linkUrl: '', animation: 'fade-up' },
                { id: 'el_3', type: 'button', text: 'SHOP NOW →', x: 12, y: 70, font: 'Inter', size: 15, weight: '800', color: '#ffffff', bg: '#6B1E3F', align: 'center', italic: false, underline: false, uppercase: true, linkHas: true, linkUrl: '/shop', animation: 'bounce', radius: 30, padding: 10 }
            ];
        }
        pushHistory();
        renderCanvas();
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
        const fontScale = isMobileMode ? 0.45 : 1.0;

        elements.forEach(item => {
            const elNode = document.createElement('div');
            elNode.className = `qw-element-item ${selectedElementId === item.id ? 'selected' : ''}`;
            elNode.id = `node_${item.id}`;
            elNode.style.left = `${item.x}%`;
            elNode.style.top = `${item.y}%`;
            elNode.style.fontFamily = item.font || 'Inter';
            
            const computedSize = Math.max(10, Math.round((item.size || 20) * fontScale));
            elNode.style.fontSize = `${computedSize}px`;
            elNode.style.fontWeight = item.weight || '400';
            elNode.style.color = item.color || '#000000';
            elNode.style.backgroundColor = item.bg || 'transparent';
            elNode.style.textAlign = item.align || 'left';
            elNode.style.fontStyle = item.italic ? 'italic' : 'normal';
            elNode.style.textDecoration = item.underline ? 'underline' : 'none';
            elNode.style.textTransform = item.uppercase ? 'uppercase' : 'none';
            elNode.style.borderRadius = `${item.radius || 4}px`;
            
            const padVal = item.padding || 4;
            const scaledPad = isMobileMode ? Math.max(2, Math.round(padVal * 0.5)) : padVal;
            elNode.style.padding = `${scaledPad}px ${scaledPad * 1.5}px`;
            elNode.textContent = item.text;

            // Selection Event
            elNode.addEventListener('pointerdown', (e) => {
                e.stopPropagation();
                selectElement(item.id);
                startDrag(e, item);
            });

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
        document.getElementById('propFontSize').value = item.size || 24;
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
    document.getElementById('propFontSize')?.addEventListener('input', (e) => {
        updateActiveProp('size', parseInt(e.target.value) || 20);
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
            item[key] = value;
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
        const origX = item.x;
        const origY = item.y;

        function onPointerMove(moveEvent) {
            if (!isDragging) return;
            const deltaX = moveEvent.clientX - startX;
            const deltaY = moveEvent.clientY - startY;

            let newX = origX + (deltaX / frameRect.width) * 100;
            let newY = origY + (deltaY / frameRect.height) * 100;

            item.x = Math.max(0, Math.min(92, newX));
            item.y = Math.max(0, Math.min(92, newY));

            const activeNode = document.getElementById(`node_${item.id}`);
            if (activeNode) {
                activeNode.style.left = `${item.x}%`;
                activeNode.style.top = `${item.y}%`;
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
                canvasBgImage.src = e.target.result;
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
        canvasFrame.classList.remove('mobile-mode');
        if (mobileGuide) mobileGuide.classList.add('d-none');
        renderCanvas();
    });

    document.getElementById('btnViewMobile')?.addEventListener('click', () => {
        document.getElementById('btnViewMobile').classList.add('active');
        document.getElementById('btnViewDesktop').classList.remove('active');
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
    document.getElementById('btnPublish')?.addEventListener('click', () => saveSlide('active'));
    document.getElementById('btnSaveDraft')?.addEventListener('click', () => saveSlide('inactive'));

    function saveSlide(status) {
        if (!currentSlideId) {
            alert('Please select or create a slide first.');
            return;
        }

        const payload = {
            slide_id: currentSlideId,
            section_key: currentSectionKey,
            status: status,
            overlay_items: elements
        };

        fetch("{{ route('admin.home-carousel.builder.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
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
            window.location.href = card.dataset.slideUrl;
        });
    });

    // Add Slide Modal Trigger
    document.getElementById('btnAddSlideModal')?.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('modalAddSlide'));
        modal.show();
    });

    // Form Add Slide Submission
    document.getElementById('formAddSlide')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('status', 'active');
        formData.append('overlay_items', JSON.stringify([]));

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
                alert('Error creating slide: ' + (data.message || 'Unknown error'));
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
