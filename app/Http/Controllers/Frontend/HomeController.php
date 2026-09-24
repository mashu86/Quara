<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeContent;
use App\Models\HomeCarouselSetting;
use App\Models\HomeCarouselSlide;
use App\Models\HomePageSection;
use App\Models\HomeTestimonial;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Setting;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $homeContent = HomeContent::select(['id', 'title', 'content_html', 'custom_css', 'image_position', 'status', 'image_mime'])
            ->where('status', 'active')
            ->first();

        $carouselSettings = HomeCarouselSetting::first();
        $homeSections = HomePageSection::orderBy('sort_order')->get()->keyBy('section_key');

        $categories = Category::publicActive()->withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'desc')
        ->get();

        if (!$request->ajax() && !$request->wantsJson()) {
        $carouselSlides = collect();
        $lookbookSlides = collect();
        if ($carouselSettings && $carouselSettings->enabled && $homeSections->get('hero')?->enabled) {
            $carouselSlides = HomeCarouselSlide::select(['id','section_key','heading','heading_color','heading_font','subheading','subheading_color','image_mime','sort_order','status','heading_size','subheading_font','subheading_size','button_text','link_url','text_x','text_y','heading_x','heading_y','subheading_x','subheading_y','button_x','button_y','overlay_items'])
                ->where('section_key', 'hero')->where('status', 'active')->orderBy('sort_order')->orderBy('id')
                ->limit(min($carouselSettings->visible_count, $homeSections->get('hero')->items_to_show))->get();
        }
        if ($homeSections->get('lookbook')?->enabled) {
            $lookbookSlides = HomeCarouselSlide::select(['id','section_key','heading','heading_color','heading_font','subheading','subheading_color','image_mime','sort_order','status','heading_size','subheading_font','subheading_size','button_text','link_url','text_x','text_y','heading_x','heading_y','subheading_x','subheading_y','button_x','button_y','overlay_items'])
                ->where('section_key', 'lookbook')->where('status', 'active')->orderBy('sort_order')->orderBy('id')
                ->limit($homeSections->get('lookbook')->items_to_show)->get();
        }

        $carouselProducts = function ($query, $limit) {
            return $query->with(['images', 'sizes'])->inStockFirst()->limit($limit)->get();
        };
        $sectionProducts = [];
        $newSection = $homeSections->get('new_arrivals');
        if ($newSection?->enabled) {
            $sectionProducts['new_arrivals'] = $carouselProducts(Product::active()->orderByDesc('created_at')->orderByDesc('id'), $newSection->items_to_show);
        }
        $bestSection = $homeSections->get('best_sellers');
        if ($bestSection?->enabled) {
            $popularIds = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereNotNull('order_items.product_id')->where('orders.order_status', '!=', 'cancelled')
                ->where(function ($query) { $query->where('orders.payment_status', 'paid')->orWhere('orders.order_status', 'delivered'); })
                ->select('order_items.product_id')->selectRaw('SUM(order_items.quantity) as sold_units')
                ->groupBy('order_items.product_id')->orderByDesc('sold_units')->limit($bestSection->items_to_show)->pluck('product_id')->map(fn($id) => (int)$id)->all();
            $popular = $popularIds ? Product::active()->whereIn('id', $popularIds)->with(['images','sizes'])->get()->keyBy('id') : collect();
            $sectionProducts['best_sellers'] = collect($popularIds)->map(fn($id) => $popular->get($id))->filter()->values();
        }
        $offerSection = $homeSections->get('offers');
        if ($offerSection?->enabled) {
            $sectionProducts['offers'] = $carouselProducts(Product::active()->where(function ($query) {
                $query->whereColumn('final_price', '<', 'price')
                    ->orWhereHas('comboCategory', fn($category) => $category->where('is_active_offer', true))
                    ->orWhereHas('categories', fn($category) => $category->where('is_active_offer', true));
            })->orderByRaw('(price - final_price) DESC')->orderByDesc('id'), $offerSection->items_to_show);
        }
        $reviewSection = $homeSections->get('reviews');
        $homeTestimonials = $reviewSection?->enabled
            ? HomeTestimonial::where('status', 'active')->orderBy('sort_order')->orderBy('id')->limit($reviewSection->items_to_show)->get()
            : collect();
        $instagramLink = SocialMedia::where('type', 'instagram')->where('status', 'active')->orderBy('sort_order')->first();
        }

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

        // Sorting: Always show In-Stock products first, Sold Out products last
        $query->inStockFirst();

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
        $categoryDisplayStyle = Setting::get('category_display_style', 'carousel');

        $seoTitle = 'Quara Wardrobe | Online Fashion Store & Ladies Wear';
        $seoDescription = 'Shop elegant, trendy & affordable ladies fashion, western wear, Korean tops, and stylish dresses at Quara Wardrobe online store. Fast pan-India delivery.';
        $canonicalUrl = route('home');

        return view('frontend.home', compact(
            'homeContent',
            'carouselSettings',
            'carouselSlides',
            'homeSections',
            'lookbookSlides',
            'sectionProducts',
            'homeTestimonials',
            'instagramLink',
            'categories',
            'products',
            'allSizes',
            'currentCategory',
            'displayOrderBy',
            'categoryDisplayStyle',
            'seoTitle',
            'seoDescription',
            'canonicalUrl'
        ));
    }
}
