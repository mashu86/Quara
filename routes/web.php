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
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\IncomeController as AdminIncomeController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\ManualSalesController as AdminManualSalesController;
use App\Http\Controllers\Admin\PaymentCheckController as AdminPaymentCheckController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\DisplayOrderController as AdminDisplayOrderController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\StorageFileController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| CUSTOMER FRONTEND & SEO ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'categoryProducts'])->name('category.products');
Route::get('/product/{slug}', [ProductDetailController::class, 'show'])->name('product.detail');
Route::get('/product/{slug}/check-shipping', [ProductDetailController::class, 'checkShipping'])->name('product.check-shipping');
Route::get('/home-content/image/{homeContent}', [AdminHomeContentController::class, 'showImage'])->name('home_content.image');
Route::get('/media/{path}', [StorageFileController::class, 'show'])->where('path', '.*')->name('media.show');
Route::get('/storage/{path}', [StorageFileController::class, 'show'])->where('path', '.*')->name('storage.file');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{cartKey}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartKey}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buy_now');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/fetch-address', [CheckoutController::class, 'fetchAddressByEmail'])->name('checkout.fetch_address');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/verify-online-payment', [CheckoutController::class, 'verifyOnlinePayment'])->name('checkout.verify_online_payment');
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

        // Product Master
        Route::resource('products', AdminProductController::class);
        Route::post('products/{product}/toggle-out-of-stock', [AdminProductController::class, 'toggleOutOfStock'])->name('products.toggle-out-of-stock');
        Route::post('products/{product}/add-stock-batch', [AdminProductController::class, 'addStockBatch'])->name('products.add-stock-batch');
        Route::post('product-images/{image}/set-primary', [AdminProductController::class, 'setPrimaryImage'])->name('product-images.set-primary');
        Route::delete('product-images/{image}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');

        // Display Preference & Drag-and-Drop Sorting
        Route::get('/display-order', [AdminDisplayOrderController::class, 'index'])->name('display-order.index');
        Route::post('/display-order/update-preference', [AdminDisplayOrderController::class, 'updatePreference'])->name('display-order.update-preference');
        Route::post('/display-order/update-category-order', [AdminDisplayOrderController::class, 'updateCategoryOrder'])->name('display-order.update-category-order');
        Route::post('/display-order/update-product-order', [AdminDisplayOrderController::class, 'updateProductOrder'])->name('display-order.update-product-order');

        // Home Main Content Master
        Route::resource('home-content', AdminHomeContentController::class)->parameters(['home-content' => 'home_content']);

        // Social Media Master
        Route::resource('social-media', AdminSocialMediaController::class)->parameters(['social-media' => 'social_media']);

        // Orders & Manual Offline Sales Management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/courier-dispatch', [AdminOrderController::class, 'updateCourierDispatch'])->name('orders.update-courier-dispatch');
        Route::post('/orders/{order}/toggle-cancellation-lock', [AdminOrderController::class, 'toggleCancellationLock'])->name('orders.toggle-cancellation-lock');
        Route::post('/orders/{order}/send-followup-email', [AdminOrderController::class, 'sendFollowupEmail'])->name('orders.send-followup-email');
        Route::post('/orders/{order}/increment-wa-count', [AdminOrderController::class, 'incrementWaCount'])->name('orders.increment-wa-count');

        Route::get('/manual-sales', [AdminManualSalesController::class, 'index'])->name('manual-sales.index');
        Route::get('/manual-sales/create', [AdminManualSalesController::class, 'create'])->name('manual-sales.create');
        Route::post('/manual-sales', [AdminManualSalesController::class, 'store'])->name('manual-sales.store');
        Route::get('/manual-sales/{order}/edit', [AdminManualSalesController::class, 'edit'])->name('manual-sales.edit');
        Route::put('/manual-sales/{order}', [AdminManualSalesController::class, 'update'])->name('manual-sales.update');

        // Order Returns & Post-Order Operations Module
        Route::get('/order-operations', [\App\Http\Controllers\Admin\OrderOperationController::class, 'index'])->name('order-operations.index');
        Route::get('/orders/{order}/operation/create', [\App\Http\Controllers\Admin\OrderOperationController::class, 'create'])->name('order-operations.create');
        Route::post('/orders/{order}/operation', [\App\Http\Controllers\Admin\OrderOperationController::class, 'store'])->name('order-operations.store');
        Route::get('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'show'])->name('order-operations.show');
        Route::get('/order-operations/{operation}/edit', [\App\Http\Controllers\Admin\OrderOperationController::class, 'edit'])->name('order-operations.edit');
        Route::put('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'update'])->name('order-operations.update');
        Route::post('/order-operations/{operation}/toggle-status', [\App\Http\Controllers\Admin\OrderOperationController::class, 'toggleStatus'])->name('order-operations.toggle-status');
        Route::delete('/order-operations/{operation}', [\App\Http\Controllers\Admin\OrderOperationController::class, 'destroy'])->name('order-operations.destroy');

        // Shipping Policy (Delivery Price Master)
        Route::resource('shipping-policies', \App\Http\Controllers\Admin\ShippingPolicyController::class);

        // Expenses & Profit/Loss Financial Management
        Route::resource('expenses', AdminExpenseController::class);
        Route::resource('incomes', AdminIncomeController::class);
        Route::post('incomes/{income}/toggle-status', [AdminIncomeController::class, 'toggleStatus'])->name('incomes.toggle-status');
        Route::get('/reports/profit-loss', [AdminExpenseController::class, 'profitLossReport'])->name('reports.profit-loss');
        Route::get('/reports/razorpay-charges', [AdminExpenseController::class, 'razorpayReport'])->name('reports.razorpay-charges');

        // Master Settings (branding, email and payment configuration)
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

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
    });
});
