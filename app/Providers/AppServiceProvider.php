<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\SocialMedia;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Share layout data with views
        View::composer('layouts.app', function ($view) {
            $navCategories = Category::where('status', 'active')->select(['id', 'name', 'slug'])->get();
            $whatsappObj = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();
            $socialLinks = SocialMedia::where('status', 'active')->orderBy('sort_order')->get();

            $view->with(compact('navCategories', 'whatsappObj', 'socialLinks'));
        });
    }
}

