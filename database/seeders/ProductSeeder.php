<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $koreanCat = Category::where('slug', 'korean-tops')->first();
        $westernCat = Category::where('slug', 'western-dresses')->first();
        $casualCat = Category::where('slug', 'casual-tops')->first();
        $partyCat = Category::where('slug', 'party-wear')->first();

        $productsData = [
            [
                'category_id' => $koreanCat ? $koreanCat->id : 1,
                'name' => 'Korean Style Casual Puff Sleeve Top',
                'price' => 999.00,
                'discount_type' => 'percentage',
                'discount_value' => 20.00, // Final: 799.20
                'description' => '<p>Chic and ultra-comfortable <strong>Korean style puff sleeve top</strong>. Crafted from premium breathable cotton-blend fabric. Perfect for daily casual outings and college wear.</p>',
                'sizes' => ['S' => 5, 'M' => 8, 'L' => 10, 'XL' => 4],
                'images' => [
                    'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1564257577054-6e3f7e7c6d1c?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category_id' => $koreanCat ? $koreanCat->id : 1,
                'name' => 'Pastel Floral Ribbed Knit Crop Top',
                'price' => 799.00,
                'discount_type' => 'fixed',
                'discount_value' => 100.00, // Final: 699.00
                'description' => '<p>Soft ribbed stretch knit crop top featuring delicate pastel floral highlights. Pair with high-waisted denim for an effortless trendy look.</p>',
                'sizes' => ['XS' => 3, 'S' => 6, 'M' => 7, 'L' => 5],
                'images' => [
                    'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1605763240000-7e93b172d754?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category_id' => $westernCat ? $westernCat->id : 2,
                'name' => 'Floral Print A-Line Midi Dress',
                'price' => 1499.00,
                'discount_type' => 'percentage',
                'discount_value' => 25.00, // Final: 1124.25
                'description' => '<p>Elegantly tailored floral midi dress with a flattering tie-up waist and flutter sleeves. Designed for daytime brunches and weekend getaways.</p>',
                'sizes' => ['S' => 4, 'M' => 9, 'L' => 6, 'XL' => 2],
                'images' => [
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category_id' => $partyCat ? $partyCat->id : 4,
                'name' => 'Satin Wrap Party Bodycon Dress',
                'price' => 1899.00,
                'discount_type' => 'fixed',
                'discount_value' => 300.00, // Final: 1599.00
                'description' => '<p>Stunning satin wrap bodycon dress with gold accent hardware. Soft sheen drape offers a sleek, captivating evening look.</p>',
                'sizes' => ['S' => 2, 'M' => 5, 'L' => 3],
                'images' => [
                    'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category_id' => $casualCat ? $casualCat->id : 3,
                'name' => 'Oversized Vintage Graphic Cotton Tee',
                'price' => 599.00,
                'discount_type' => 'none',
                'discount_value' => 0.00, // Final: 599.00
                'description' => '<p>Ultra-soft 100% bio-washed cotton relaxed oversized graphic t-shirt. Breathable, durable, and streetwear essential.</p>',
                'sizes' => ['Free Size' => 15],
                'images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=85',
                ],
            ],
        ];

        foreach ($productsData as $data) {
            $sizes = $data['sizes'];
            $images = $data['images'];
            unset($data['sizes']);
            unset($data['images']);

            $data['slug'] = Str::slug($data['name']);
            $data['status'] = 'active';

            $product = Product::create($data);

            foreach ($images as $sortOrder => $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imageUrl,
                    'is_primary' => $sortOrder === 0,
                    'sort_order' => $sortOrder,
                ]);
            }

            // Add size wise stock
            foreach ($sizes as $sizeName => $qty) {
                ProductSize::create([
                    'product_id' => $product->id,
                    'size' => $sizeName,
                    'stock' => $qty,
                ]);
            }
        }
    }
}
