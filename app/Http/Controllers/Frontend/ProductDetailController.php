<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SocialMedia;
use App\Services\ShippingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductDetailController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with(['category', 'images', 'sizes'])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->with(['category', 'images'])
            ->take(4)
            ->get();

        $whatsapp = SocialMedia::where('type', 'whatsapp')->where('status', 'active')->first();

        // Calculate discount percentage badge
        $discountPercentage = 0;
        if ($product->price > 0 && $product->discount_type !== 'none') {
            $discountPercentage = round((($product->price - $product->final_price) / $product->price) * 100);
        }

        // Product SEO & Canonical URL
        $seoTitle = $product->name . ' - Buy Online | Quara Wardrobe';
        $seoDescription = Str::limit(strip_tags($product->description), 155, '...');
        $canonicalUrl = route('product.detail', $product->slug);
        $ogImage = $product->primary_image_url;

        return view('frontend.product_detail', compact(
            'product',
            'relatedProducts',
            'whatsapp',
            'discountPercentage',
            'seoTitle',
            'seoDescription',
            'canonicalUrl',
            'ogImage'
        ));
    }

    public function checkShipping(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $pincode = $request->get('pincode', '670582');

        $calculator = app(ShippingCalculatorService::class);
        $result = $calculator->calculateRate(
            $pincode,
            (float) ($product->weight_kg ?? 0.30),
            ($product->delivery_charge_type === 'exclude')
        );

        return response()->json($result);
    }
}
