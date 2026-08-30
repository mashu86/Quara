<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeContent;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Setting;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
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

        // AJAX Infinite Scroll Response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('frontend.partials.product_grid_items', [
                'products' => $products,
                'isAjax' => true
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'has_more' => $products->hasMorePages(),
                'next_page' => $products->currentPage() + 1,
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'count_text' => 'Showing ' . ($products->firstItem() ?? 0) . '–' . ($products->lastItem() ?? 0) . ' of ' . $products->total() . ' trendy pieces at Quara Wardrobe',
            ]);
        }

        $allSizes = ProductSize::select('size')->distinct()->pluck('size');

        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = $categories->firstWhere('slug', $request->category);
        }

        $displayOrderBy = Setting::get('default_display_order_by', 'category');

        $seoTitle = 'Quara Wardrobe | Online Fashion Store & Ladies Wear';
        $seoDescription = 'Shop elegant, trendy & affordable ladies fashion, western wear, Korean tops, and stylish dresses at Quara Wardrobe online store. Fast pan-India delivery.';
        $canonicalUrl = route('home');

        return view('frontend.home', compact(
            'homeContent',
            'categories',
            'products',
            'allSizes',
            'currentCategory',
            'displayOrderBy',
            'seoTitle',
            'seoDescription',
            'canonicalUrl'
        ));
    }
}
