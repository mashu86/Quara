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
        $homeContent = HomeContent::select(['id', 'title', 'content_html', 'custom_css', 'image_position', 'status', 'image_mime'])
            ->where('status', 'active')
            ->first();
        $categories = Category::where('status', 'active')->withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->get();
        $featuredProducts = Product::active()
            ->with(['category', 'images', 'sizes'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        return view('frontend.home', compact('homeContent', 'categories', 'featuredProducts'));
    }
}
