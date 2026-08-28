<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap.
     */
    public function index(): Response
    {
        $categories = Category::where('status', 'active')->select(['id', 'slug', 'updated_at'])->get();
        $products = Product::active()->select(['id', 'slug', 'updated_at'])->get();

        $content = view('frontend.sitemap', compact('categories', 'products'))->render();

        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Generate dynamic robots.txt.
     */
    public function robots(): Response
    {
        $baseUrl = url('/');
        $sitemapUrl = url('/sitemap.xml');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /cart\n";
        $content .= "Disallow: /checkout\n";
        $content .= "Disallow: /checkout/*\n";
        $content .= "Disallow: /email-auth/*\n";
        $content .= "Disallow: /my-orders\n";
        $content .= "Disallow: /order-tracking\n\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
