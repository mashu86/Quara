<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeContent;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SocialMedia;

class HomeController extends Controller
{
    public function index()
    {
        $homeContent = HomeContent::select(['id', 'title', 'content_html', 'custom_css', 'image_position', 'status', 'image_mime'])
            ->where('status', 'active')
            ->first();
        $categories = Category::where('status', 'active')->withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'desc')
        ->get();

        $featuredProducts = Product::active()
            ->with(['category', 'images', 'sizes'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $displayOrderBy = Setting::get('default_display_order_by', 'category');

        $seoTitle = 'Quara Wardrobe | Online Fashion Store & Ladies Wear';
        $seoDescription = 'Shop elegant, trendy & affordable ladies fashion, western wear, Korean tops, and stylish dresses at Quara Wardrobe online store. Fast pan-India delivery.';
        $canonicalUrl = route('home');

        return view('frontend.home', compact(
            'homeContent',
            'categories',
            'featuredProducts',
            'displayOrderBy',
            'seoTitle',
            'seoDescription',
            'canonicalUrl'
        ));
    }
}
