<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with(['category', 'categories', 'images', 'sizes']);

        // General search box (partial matching product name or category name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('categories', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $categorySlug = $request->category;
            $query->where(function ($q) use ($categorySlug) {
                $q->whereHas('category', function ($cq) use ($categorySlug) {
                    $cq->where('slug', $categorySlug);
                })->orWhereHas('categories', function ($cq) use ($categorySlug) {
                    $cq->where('slug', $categorySlug);
                });
            });
        }

        // Min & Max price filters
        if ($request->filled('min_price')) {
            $query->where('final_price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('final_price', '<=', (float) $request->max_price);
        }

        // Size filter
        if ($request->filled('size')) {
            $sizeVal = $request->size;
            $query->where('is_out_of_stock', false)
                ->whereHas('sizes', function ($q) use ($sizeVal) {
                    $q->where('size', $sizeVal)->where('stock', '>', 0);
                });
        }

        // Stock status filter
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $query->where('is_out_of_stock', false)
                    ->whereHas('sizes', function ($q) {
                        $q->where('stock', '>', 0);
                    });
            } elseif ($request->stock === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where('is_out_of_stock', true)
                      ->orWhereDoesntHave('sizes', function ($sq) {
                          $sq->where('stock', '>', 0);
                      });
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_low') {
            $query->orderBy('final_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('final_price', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();
        $allSizes = ProductSize::select('size')->distinct()->pluck('size');

        // Dynamic SEO Metadata & Canonical URL Handling
        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = $categories->firstWhere('slug', $request->category);
        }

        if ($currentCategory) {
            $seoTitle = $currentCategory->name . ' - Buy Women\'s Western Wear Online | Quara Wardrobe';
            $seoDescription = 'Explore elegant and trendy ' . strtolower($currentCategory->name) . ' at Quara Wardrobe online shop. High fashion ladies wear with fast pan-India shipping.';
            $canonicalUrl = route('category.products', $currentCategory->slug);
        } else {
            $seoTitle = 'Shop All Ladies Fashion & Western Wear | Quara Wardrobe';
            $seoDescription = 'Browse the complete collection of stylish Korean tops, western dresses, and everyday ladies apparel at Quara Wardrobe online store.';
            $canonicalUrl = route('shop');
        }

        return view('frontend.shop', compact(
            'products',
            'categories',
            'allSizes',
            'currentCategory',
            'seoTitle',
            'seoDescription',
            'canonicalUrl'
        ));
    }

    public function categoryProducts(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $request->merge(['category' => $slug]);
        return $this->index($request);
    }
}
