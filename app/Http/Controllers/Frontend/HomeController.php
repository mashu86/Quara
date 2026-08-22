<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeContent;
use App\Models\Product;
use App\Models\SocialMedia;

class HomeController extends Controller
{
    public function index()
    {
        $homeContent = HomeContent::where('status', 'active')->first();
        $categories = Category::where('status', 'active')->withCount('products')->get();
        $featuredProducts = Product::where('status', 'active')
            ->with(['category', 'images', 'sizes'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $whatsapp = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();

        return view('frontend.home', compact('homeContent', 'categories', 'featuredProducts', 'whatsapp'));
    }
}
