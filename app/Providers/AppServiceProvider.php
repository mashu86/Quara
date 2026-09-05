<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SocialMedia;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        date_default_timezone_set('Asia/Kolkata');
        Paginator::useBootstrapFive();

        $settings = [];

        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::values();
            }
        } catch (Throwable) {
            // Keep environment defaults available during installation or DB outages.
        }

        $siteName = $settings['site_name'] ?? config('app.name', 'QUARA WARDROBE');
        $supportEmail = $settings['mail_from_address'] ?? config('mail.from.address');
        $siteLogoUrl = Setting::logoUrl($settings);
        $siteFaviconUrl = Setting::faviconUrl($settings);
        $siteLogoPath = Setting::logoPath($settings);

        View::share(compact('siteName', 'supportEmail', 'siteLogoUrl', 'siteFaviconUrl', 'siteLogoPath'));

        if (! empty($settings['mail_host'])) {
            $encryption = $settings['mail_encryption'] ?? 'tls';
            if (str_contains(strtolower($settings['mail_host']), 'gmail.com')) {
                $encryption = (int) ($settings['mail_port'] ?? 587) === 465 ? 'ssl' : 'tls';
            }
            $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.scheme' => $scheme,
                'mail.mailers.smtp.host' => $settings['mail_host'],
                'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? 587),
                'mail.mailers.smtp.username' => $settings['mail_username'] ?? null,
                'mail.mailers.smtp.password' => Setting::decryptSecret($settings['mail_password'] ?? null)
                    ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
                'mail.mailers.smtp.auto_tls' => $encryption !== 'none',
                'mail.mailers.smtp.require_tls' => $encryption === 'tls',
                'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?? $siteName,
            ]);
        }

        if (! empty($settings['razorpay_key'])) {
            config([
                'services.razorpay.key' => $settings['razorpay_key'],
                'services.razorpay.secret' => Setting::decryptSecret($settings['razorpay_secret'] ?? null)
                    ?? config('services.razorpay.secret'),
            ]);
        }

        // Share layout data with views
        View::composer('layouts.app', function ($view) {
            $navCategories = Category::where('status', 'active')->select(['id', 'name', 'slug'])->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();
            $whatsappObj = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();
            $socialLinks = SocialMedia::where('status', 'active')->orderBy('sort_order')->get();

            $view->with(compact('navCategories', 'whatsappObj', 'socialLinks'));
        });

        View::composer('layouts.admin', function ($view) {
            $pendingCount = Order::where('order_status', 'pending')->count();
            $unreadOrderCount = Notification::where('is_read', false)->whereNotNull('order_id')->count();
            $unreadCount = Notification::where('is_read', false)->count();
            $recentNotifications = Notification::with('order')->orderBy('id', 'desc')->take(5)->get();

            $view->with(compact('pendingCount', 'unreadOrderCount', 'unreadCount', 'recentNotifications'));
        });
    }
}
