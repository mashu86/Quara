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
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\ManualSalesController as AdminManualSalesController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| CUSTOMER FRONTEND ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'categoryProducts'])->name('category.products');
Route::get('/product/{slug}', [ProductDetailController::class, 'show'])->name('product.detail');
Route::get('/product/{slug}/check-shipping', [ProductDetailController::class, 'checkShipping'])->name('product.check-shipping');
Route::get('/home-content/image/{homeContent}', [AdminHomeContentController::class, 'showImage'])->name('home_content.image');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{cartKey}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartKey}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buy_now');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/verify-online-payment', [CheckoutController::class, 'verifyOnlinePayment'])->name('checkout.verify_online_payment');
Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

// Order Tracking
Route::get('/order-tracking', [FrontendOrderController::class, 'track'])->name('order.tracking');


/*
|--------------------------------------------------------------------------
| ADMIN PANEL ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest Admin Auth Routes
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

        // Category Master
        Route::resource('categories', AdminCategoryController::class);
        Route::post('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Product Master
        Route::resource('products', AdminProductController::class);
        Route::post('products/{product}/add-stock-batch', [AdminProductController::class, 'addStockBatch'])->name('products.add-stock-batch');
        Route::post('product-images/{image}/set-primary', [AdminProductController::class, 'setPrimaryImage'])->name('product-images.set-primary');
        Route::delete('product-images/{image}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');

        // Home Main Content Master
        Route::resource('home-content', AdminHomeContentController::class);

        // Social Media Master
        Route::resource('social-media', AdminSocialMediaController::class);

        // Orders & Manual Offline Sales Management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/courier-dispatch', [AdminOrderController::class, 'updateCourierDispatch'])->name('orders.update-courier-dispatch');
        Route::post('/orders/{order}/toggle-cancellation-lock', [AdminOrderController::class, 'toggleCancellationLock'])->name('orders.toggle-cancellation-lock');

        Route::get('/manual-sales', [AdminManualSalesController::class, 'index'])->name('manual-sales.index');
        Route::get('/manual-sales/create', [AdminManualSalesController::class, 'create'])->name('manual-sales.create');
        Route::post('/manual-sales', [AdminManualSalesController::class, 'store'])->name('manual-sales.store');

        // Shipping Policy (Delivery Price Master)
        Route::resource('shipping-policies', \App\Http\Controllers\Admin\ShippingPolicyController::class);

        // Expenses & Profit/Loss Financial Management
        Route::resource('expenses', AdminExpenseController::class)->except(['show', 'edit', 'update']);
        Route::get('/reports/profit-loss', [AdminExpenseController::class, 'profitLossReport'])->name('reports.profit-loss');

        // Order Invoice Printable View
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });
});
