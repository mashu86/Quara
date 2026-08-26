<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\SocialMedia;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Share layout data with views
        View::composer('layouts.app', function ($view) {
            $navCategories = Category::where('status', 'active')->select(['id', 'name', 'slug'])->get();
            $whatsappObj = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();
            $socialLinks = SocialMedia::where('status', 'active')->orderBy('sort_order')->get();

            $view->with(compact('navCategories', 'whatsappObj', 'socialLinks'));
        });

        View::composer('layouts.admin', function ($view) {
            $pendingCount = \App\Models\Order::where('order_status', 'pending')->count();
            $unreadCount = \App\Models\Notification::where('is_read', false)->count();
            $recentNotifications = \App\Models\Notification::with('order')->orderBy('id', 'desc')->take(5)->get();

            $view->with(compact('pendingCount', 'unreadCount', 'recentNotifications'));
        });
    }
}

