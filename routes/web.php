<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HomeContentController as AdminHomeContentController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SocialMediaController as AdminSocialMediaController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController as FrontendOrderController;
use App\Http\Controllers\Frontend\ProductDetailController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\SizeGuideController;
use App\Http\Controllers\Admin\CapitalController as AdminCapitalController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\IncomeController as AdminIncomeController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\ManualSalesController as AdminManualSalesController;
use App\Http\Controllers\Admin\PaymentCheckController as AdminPaymentCheckController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\DisplayOrderController as AdminDisplayOrderController;
use App\Http\Controllers\Admin\OfferSaleController as AdminOfferSaleController;
use App\Http\Controllers\Admin\BulkComboCategoryController as AdminBulkComboCategoryController;
use App\Http\Controllers\Admin\PaymentDiscrepancyController as AdminPaymentDiscrepancyController;
use App\Http\Controllers\Admin\DailyJournalController as AdminDailyJournalController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Admin\ContractualPostController;
use App\Http\Controllers\StorageFileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\LuckyWinnerController;
use App\Http\Controllers\Admin\GeminiApiKeyController as AdminGeminiApiKeyController;
use App\Http\Controllers\Admin\HomeCarouselController as AdminHomeCarouselController;
use App\Http\Middleware\LuckyWinnerAccess;

Route::prefix('luckywinner')->name('luckywinner.')->middleware(LuckyWinnerAccess::class)->group(function () {
    Route::get('/', [LuckyWinnerController::class, 'index'])->name('index');
    Route::post('/prepare', [LuckyWinnerController::class, 'prepare'])->name('prepare');
    Route::post('/draft/{token}/select', [LuckyWinnerController::class, 'select'])->whereUuid('token')->name('select');
    Route::post('/draft/{token}/store', [LuckyWinnerController::class, 'store'])->whereUuid('token')->name('store');
    Route::get('/history', fn () => redirect()->route('admin.luckywinner.history'))->name('history');
    Route::get('/history/{draw}', fn (\App\Models\LuckyDraw $draw) => redirect()->route('admin.luckywinner.show', $draw))->name('show');
});

Route::prefix('admin/luckywinner')->name('admin.luckywinner.')->middleware(LuckyWinnerAccess::class)->group(function () {
    Route::get('/history', [LuckyWinnerController::class, 'history'])->name('history');
    Route::get('/history/{draw}', [LuckyWinnerController::class, 'show'])->name('show');
    Route::delete('/history/{draw}', [LuckyWinnerController::class, 'destroy'])->name('destroy');
    Route::post('/history/{draw}/update-title', [LuckyWinnerController::class, 'updateTitle'])->name('update-title');
    Route::post('/toggle-visibility', [LuckyWinnerController::class, 'toggleVisibility'])->name('toggle-visibility');
});

// Razorpay Asynchronous Webhook Route (CSRF Exempted in bootstrap/app.php)
Route::post('api/webhooks/razorpay', [\App\Http\Controllers\Api\RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

/*
|--------------------------------------------------------------------------
| CUSTOMER FRONTEND & SEO ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/products/size-guide', [SizeGuideController::class, 'index'])->name('products.size-guide');
Route::get('/category/{slug}', [ShopController::class, 'categoryProducts'])->name('category.products');
Route::get('/product/{slug}', [ProductDetailController::class, 'show'])->name('product.detail');
Route::get('/product/{slug}/check-shipping', [ProductDetailController::class, 'checkShipping'])->name('product.check-shipping');
Route::get('/home-content/image/{homeContent}', [AdminHomeContentController::class, 'showImage'])->name('home_content.image');
Route::get('/home-carousel/image/{slide}', [AdminHomeCarouselController::class, 'showImage'])->name('home_carousel.image');
Route::get('/media/{path}', [StorageFileController::class, 'show'])->where('path', '.*')->name('media.show');
Route::get('/storage/{path}', [StorageFileController::class, 'show'])->where('path', '.*')->name('storage.file');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/add-combo', [CartController::class, 'addCombo'])->name('cart.add_combo');
Route::post('/cart/update/{cartKey}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartKey}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buy_now');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/fetch-address', [CheckoutController::class, 'fetchAddressByEmail'])->name('checkout.fetch_address');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/verify-online-payment', [CheckoutController::class, 'verifyOnlinePayment'])->name('checkout.verify_online_payment');
Route::post('/api/webhooks/razorpay', [\App\Http\Controllers\Api\RazorpayWebhookController::class, 'handle'])->name('api.webhooks.razorpay');
Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

// Visual Image / Screenshot Search Route
Route::post('/visual-search/upload', [\App\Http\Controllers\Frontend\VisualSearchController::class, 'search'])->name('visual.search');

// Customer Email Auth & My Orders
Route::post('/email-auth/send-otp', [\App\Http\Controllers\Frontend\EmailAuthController::class, 'sendOtp'])->name('email.send-otp');
Route::post('/email-auth/verify-otp', [\App\Http\Controllers\Frontend\EmailAuthController::class, 'verifyOtp'])->name('email.verify-otp');
Route::get('/my-orders', [\App\Http\Controllers\Frontend\EmailAuthController::class, 'myOrders'])->name('customer.my-orders');
Route::post('/customer/logout', [\App\Http\Controllers\Frontend\EmailAuthController::class, 'logout'])->name('customer.logout');

// Order Tracking
Route::get('/order-tracking', [FrontendOrderController::class, 'track'])->name('order.tracking');


/*
|--------------------------------------------------------------------------
| ADMIN PANEL ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest Admin Auth Routes
    Route::get('/', [AdminAuthController::class, 'showLoginForm']);
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Admin Password Reset Routes
    Route::get('/forgot-password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AdminForgotPasswordController::class, 'resetPassword'])->name('password.update');

    // Authenticated Admin Routes
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/change-password', [AdminAuthController::class, 'changePassword'])->name('change-password');

        // Category Master
        Route::resource('categories', AdminCategoryController::class);
        Route::post('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Product Master & Size Guide Master
        Route::get('products/size-guide', [\App\Http\Controllers\Admin\SizeMasterController::class, 'index'])->name('size-guide.index');
        Route::post('size-masters/categories', [\App\Http\Controllers\Admin\SizeMasterController::class, 'storeCategory'])->name('size-masters.category.store');
        Route::put('size-masters/categories/{sizeMaster}', [\App\Http\Controllers\Admin\SizeMasterController::class, 'updateCategory'])->name('size-masters.category.update');
        Route::delete('size-masters/categories/{sizeMaster}', [\App\Http\Controllers\Admin\SizeMasterController::class, 'destroyCategory'])->name('size-masters.category.destroy');
        Route::post('size-masters/categories/{sizeMaster}/rows', [\App\Http\Controllers\Admin\SizeMasterController::class, 'storeRow'])->name('size-masters.row.store');
        Route::put('size-masters/rows/{row}', [\App\Http\Controllers\Admin\SizeMasterController::class, 'updateRow'])->name('size-masters.row.update');
        Route::delete('size-masters/rows/{row}', [\App\Http\Controllers\Admin\SizeMasterController::class, 'destroyRow'])->name('size-masters.row.destroy');
        Route::get('size-masters/chart/{sizeMaster}', [\App\Http\Controllers\Admin\SizeMasterController::class, 'getChartJson'])->name('size-masters.chart-json');

        Route::post('products/ai-auto-fill', [AdminProductController::class, 'aiAutoFill'])->name('products.ai-auto-fill');
        Route::resource('products', AdminProductController::class);
        Route::get('booked-products', [AdminProductController::class, 'bookedProducts'])->name('products.booked');
        Route::get('products-booked-conflicts', [AdminProductController::class, 'bookedConflicts'])->name('products.booked-conflicts');
        Route::post('products/{product}/resolve-conflict', [AdminProductController::class, 'resolveConflict'])->name('products.resolve-conflict');
        Route::post('products/{product}/toggle-out-of-stock', [AdminProductController::class, 'toggleOutOfStock'])->name('products.toggle-out-of-stock');
        Route::post('products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('products/{product}/add-stock-batch', [AdminProductController::class, 'addStockBatch'])->name('products.add-stock-batch');
        Route::post('product-images/{image}/set-primary', [AdminProductController::class, 'setPrimaryImage'])->name('product-images.set-primary');
        Route::delete('product-images/{image}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');

        // Display Preference & Drag-and-Drop Sorting
        Route::get('/display-order', [AdminDisplayOrderController::class, 'index'])->name('display-order.index');
        Route::post('/display-order/update-preference', [AdminDisplayOrderController::class, 'updatePreference'])->name('display-order.update-preference');
        Route::post('/display-order/update-category-order', [AdminDisplayOrderController::class, 'updateCategoryOrder'])->name('display-order.update-category-order');
        Route::post('/display-order/update-product-order', [AdminDisplayOrderController::class, 'updateProductOrder'])->name('display-order.update-product-order');
        Route::post('/display-order/update-combo-product-order', [AdminDisplayOrderController::class, 'updateComboProductOrder'])->name('display-order.update-combo-product-order');

        // Offer Sale Manager
        Route::get('/offer-sale', [AdminOfferSaleController::class, 'index'])->name('offer-sale.index');
        Route::post('/offer-sale/assign', [AdminOfferSaleController::class, 'assign'])->name('offer-sale.assign');
        Route::post('/offer-sale/activate', [AdminOfferSaleController::class, 'activateOfferCategory'])->name('offer-sale.activate');
        Route::post('/offer-sale/remove-offers', [AdminOfferSaleController::class, 'removeOfferFromAllAvailableProducts'])->name('offer-sale.remove-offers');
        
        // Legacy bulk-combo-offer route alias
        Route::get('/bulk-combo-offer', fn() => redirect()->route('admin.offer-sale.index'))->name('bulk-combo-offer.index');
        Route::post('/bulk-combo-offer/assign', [AdminOfferSaleController::class, 'assign'])->name('bulk-combo-offer.assign');

        // Home Main Content Master
        Route::resource('home-content', AdminHomeContentController::class)->parameters(['home-content' => 'home_content']);

        // Home page carousel master
        Route::get('/home-carousel', [AdminHomeCarouselController::class, 'index'])->name('home-carousel.index');
        Route::post('/home-carousel/settings', [AdminHomeCarouselController::class, 'updateSettings'])->name('home-carousel.settings');
        Route::get('/home-carousel/builder/{slide?}', [AdminHomeCarouselController::class, 'builder'])->name('home-carousel.builder');
        Route::post('/home-carousel/builder/save', [AdminHomeCarouselController::class, 'saveSlideFull'])->name('home-carousel.builder.save');
        Route::post('/home-carousel/slides', [AdminHomeCarouselController::class, 'store'])->name('home-carousel.slides.store');
        Route::get('/home-carousel/slides/{slide}/edit', [AdminHomeCarouselController::class, 'edit'])->name('home-carousel.slides.edit');
        Route::put('/home-carousel/slides/{slide}', [AdminHomeCarouselController::class, 'update'])->name('home-carousel.slides.update');
        Route::post('/home-carousel/slides/{slide}/design', [AdminHomeCarouselController::class, 'saveDesign'])->name('home-carousel.slides.design');
        Route::post('/home-carousel/slides/{slide}/toggle-status', [AdminHomeCarouselController::class, 'toggleStatus'])->name('home-carousel.slides.toggle-status');
        Route::delete('/home-carousel/slides/{slide}', [AdminHomeCarouselController::class, 'destroy'])->name('home-carousel.slides.destroy');
        Route::post('/home-carousel/testimonials', [AdminHomeCarouselController::class, 'storeTestimonial'])->name('home-carousel.testimonials.store');
        Route::delete('/home-carousel/testimonials/{testimonial}', [AdminHomeCarouselController::class, 'destroyTestimonial'])->name('home-carousel.testimonials.destroy');

        // Social Media Master
        Route::resource('social-media', AdminSocialMediaController::class)->parameters(['social-media' => 'social_media']);

        // Orders & Manual Offline Sales Management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/payment-details', [AdminOrderController::class, 'updatePaymentDetails'])->name('orders.update-payment-details');
        Route::post('/orders/{order}/courier-dispatch', [AdminOrderController::class, 'updateCourierDispatch'])->name('orders.update-courier-dispatch');
        Route::post('/orders/{order}/toggle-cancellation-lock', [AdminOrderController::class, 'toggleCancellationLock'])->name('orders.toggle-cancellation-lock');
        Route::post('/orders/{order}/send-followup-email', [AdminOrderController::class, 'sendFollowupEmail'])->name('orders.send-followup-email');
        Route::post('/orders/{order}/increment-wa-count', [AdminOrderController::class, 'incrementWaCount'])->name('orders.increment-wa-count');
        Route::post('/orders/{order}/recheck-razorpay', [AdminOrderController::class, 'recheckRazorpayStatus'])->name('orders.recheck-razorpay');
        Route::post('/orders/auto-sync-pending', [AdminOrderController::class, 'autoSyncPendingOrdersAjax'])->name('orders.auto-sync-pending');

        // Razorpay Payment Discrepancies & Reconciliation Audit Module
        Route::get('/payment-discrepancies', [AdminPaymentDiscrepancyController::class, 'index'])->name('payment-discrepancies.index');
        Route::post('/payment-discrepancies/{order}/reconcile', [AdminPaymentDiscrepancyController::class, 'reconcile'])->name('payment-discrepancies.reconcile');
        Route::post('/payment-discrepancies/reconcile-all', [AdminPaymentDiscrepancyController::class, 'reconcileAll'])->name('payment-discrepancies.reconcile-all');

        // Daily Business Operations & Summary Journal Module
        Route::get('/daily-journal', [AdminDailyJournalController::class, 'index'])->name('daily-journal.index');
        Route::post('/daily-journal/release-reserved-stock', [AdminDailyJournalController::class, 'releaseReservedStock'])->name('daily-journal.release-reserved-stock');

        Route::get('/manual-sales', [AdminManualSalesController::class, 'index'])->name('manual-sales.index');
        Route::get('/manual-sales/create', [AdminManualSalesController::class, 'create'])->name('manual-sales.create');
        Route::post('/manual-sales/parse-address', [AdminManualSalesController::class, 'parseAddress'])->name('manual-sales.parse-address');
        Route::post('/manual-sales', [AdminManualSalesController::class, 'store'])->name('manual-sales.store');
        Route::get('/manual-sales/{order}/edit', [AdminManualSalesController::class, 'edit'])->name('manual-sales.edit');
        Route::put('/manual-sales/{order}', [AdminManualSalesController::class, 'update'])->name('manual-sales.update');

        // Order Returns & Post-Order Operations Module
        Route::get('/order-operations', [\App\Http\Controllers\Admin\OrderOperationController::class, 'index'])->name('order-operations.index');
        Route::get('/orders/{order}/operation/create', [\App\Http\Controllers\Admin\OrderOperationController::class, 'create'])->name('order-operations.create');
        Route::post('/orders/{order}/operation', [\App\Http\Controllers\Admin\OrderOperationController::class, 'store'])->name('order-operations.store');
        Route::post('/orders/{order}/add-item', [\App\Http\Controllers\Admin\OrderOperationController::class, 'addOrderItem'])->name('order-operations.add-item');
        Route::post('/orders/{order}/update-shipping', [\App\Http\Controllers\Admin\OrderOperationController::class, 'updateShipping'])->name('order-operations.update-shipping');
        Route::post('/orders/{order}/add-expense', [\App\Http\Controllers\Admin\OrderOperationController::class, 'addOrderExpense'])->name('order-operations.add-expense');
        Route::post('/orders/{order}/add-refund', [\App\Http\Controllers\Admin\OrderOperationController::class, 'addOrderRefund'])->name('order-operations.add-refund');
        Route::post('/orders/{order}/add-income', [\App\Http\Controllers\Admin\OrderOperationController::class, 'addOrderIncome'])->name('order-operations.add-income');
        Route::delete('/orders/{order}/items/{item}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'removeOrderItem'])->name('order-operations.remove-item');
        Route::get('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'show'])->name('order-operations.show');
        Route::get('/order-operations/{operation}/edit', [\App\Http\Controllers\Admin\OrderOperationController::class, 'edit'])->name('order-operations.edit');
        Route::put('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'update'])->name('order-operations.update');
        Route::post('/order-operations/{operation}/toggle-status', [\App\Http\Controllers\Admin\OrderOperationController::class, 'toggleStatus'])->name('order-operations.toggle-status');
        Route::delete('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'destroy'])->name('order-operations.destroy');

        // Shipping Policy (Delivery Price Master)
        Route::resource('shipping-policies', \App\Http\Controllers\Admin\ShippingPolicyController::class);

        // Capital Investment & Expenses Financial Management
        Route::resource('capitals', AdminCapitalController::class);
        Route::resource('expenses', AdminExpenseController::class);
        Route::resource('incomes', AdminIncomeController::class);
        Route::post('incomes/{income}/toggle-status', [AdminIncomeController::class, 'toggleStatus'])->name('incomes.toggle-status');
        Route::get('/reports/profit-loss', [AdminExpenseController::class, 'profitLossReport'])->name('reports.profit-loss');
        Route::get('/reports/refunded-products', [AdminExpenseController::class, 'refundedProductsReport'])->name('reports.refunded-products');
        Route::get('/reports/razorpay-charges', [AdminExpenseController::class, 'razorpayReport'])->name('reports.razorpay-charges');
        Route::get('/profit-prediction', [\App\Http\Controllers\Admin\ProfitPredictionController::class, 'index'])->name('profit-prediction.index');

        // Employee Management
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);

        // Salary Master & Settlements
        Route::get('/salary-master', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'index'])->name('salary-master.index');
        Route::post('/salary-master', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'store'])->name('salary-master.store');
        Route::get('/salary-master/{salary}/edit', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'edit'])->name('salary-master.edit');
        Route::put('/salary-master/{salary}', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'update'])->name('salary-master.update');
        Route::delete('/salary-master/{salary}', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'destroy'])->name('salary-master.destroy');
        Route::post('/salary-master/settle', [\App\Http\Controllers\Admin\SalaryMasterController::class, 'settle'])->name('salary-master.settle');

        // Contractual Post & India Post Wallet Management
        Route::get('/contractual-posts', [ContractualPostController::class, 'index'])->name('contractual-posts.index');
        Route::post('/contractual-posts/recharge', [ContractualPostController::class, 'storeRecharge'])->name('contractual-posts.recharge.store');
        Route::put('/contractual-posts/recharge/{recharge}', [ContractualPostController::class, 'updateRecharge'])->name('contractual-posts.recharge.update');
        Route::delete('/contractual-posts/recharge/{recharge}', [ContractualPostController::class, 'destroyRecharge'])->name('contractual-posts.recharge.destroy');
        Route::post('/contractual-posts/courier', [ContractualPostController::class, 'storeCourier'])->name('contractual-posts.courier.store');
        Route::put('/contractual-posts/courier/{courier}', [ContractualPostController::class, 'updateCourier'])->name('contractual-posts.courier.update');
        Route::delete('/contractual-posts/courier/{courier}', [ContractualPostController::class, 'destroyCourier'])->name('contractual-posts.courier.destroy');

        // Master Settings (branding, email and payment configuration)
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

        // Google Gemini API Keys Master Management
        Route::get('/gemini-keys', [AdminGeminiApiKeyController::class, 'index'])->name('gemini-keys.index');
        Route::post('/gemini-keys', [AdminGeminiApiKeyController::class, 'store'])->name('gemini-keys.store');
        Route::put('/gemini-keys/{geminiKey}', [AdminGeminiApiKeyController::class, 'update'])->name('gemini-keys.update');
        Route::post('/gemini-keys/{geminiKey}/activate', [AdminGeminiApiKeyController::class, 'activate'])->name('gemini-keys.activate');
        Route::delete('/gemini-keys/{geminiKey}', [AdminGeminiApiKeyController::class, 'destroy'])->name('gemini-keys.destroy');

        // Razorpay credential and ₹1 checkout verification
        Route::get('/payment_check', [AdminPaymentCheckController::class, 'index'])->name('payment-check.index');
        Route::post('/payment_check/order', [AdminPaymentCheckController::class, 'createOrder'])->name('payment-check.order');
        Route::post('/payment_check/verify', [AdminPaymentCheckController::class, 'verify'])->name('payment-check.verify');

        // Order Invoice Printable View
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });
});
