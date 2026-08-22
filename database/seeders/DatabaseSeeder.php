<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductImage;
use App\Models\SocialMedia;
use App\Models\HomeContent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Admin User
        User::updateOrCreate(
            ['username' => 'Quara'],
            [
                'name' => 'Quara Admin',
                'email' => 'admin@quarawaldrop.com',
                'phone' => '9876543210',
                'password' => Hash::make('Quara86'),
                'role' => 'admin',
            ]
        );

        // 2. Seed Categories
        $categoriesData = [
            ['name' => 'Korean Tops', 'text_color' => '#C9962E', 'background_image' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=1200&q=85'],
            ['name' => 'Western Dresses', 'text_color' => '#111111', 'background_image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=1200&q=85'],
            ['name' => 'Trendy Bottoms', 'text_color' => '#111111', 'background_image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=1200&q=85'],
            ['name' => 'Co-Ord Sets', 'text_color' => '#C9962E', 'background_image' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=1200&q=85'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::updateOrCreate(
                ['slug' => Str::slug($c['name'])],
                [
                    'name' => $c['name'],
                    'text_color' => $c['text_color'],
                    'status' => 'active'
                ]
            );
            if (!$categories[$c['name']]->background_image) {
                $categories[$c['name']]->update(['background_image' => $c['background_image']]);
            }
        }

        // 3. Seed Sample Products
        $productsData = [
            [
                'category' => 'Korean Tops',
                'name' => 'Floral Chiffon Ruffle Top',
                'price' => 799.00,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'description' => 'Lightweight floral chiffon ruffle sleeve Korean blouse. Super soft, breathable fabric perfect for college or casual outings.',
                'sizes' => ['S' => 15, 'M' => 20, 'L' => 10],
                'images' => [
                    'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1564257577054-6e3f7e7c6d1c?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category' => 'Korean Tops',
                'name' => 'Minimalist Satin Button-Down Blouse',
                'price' => 899.00,
                'discount_type' => 'fixed',
                'discount_value' => 150,
                'description' => 'Elegantly tailored Korean satin shirt in subtle champagne gold tone.',
                'sizes' => ['S' => 8, 'M' => 12, 'L' => 5],
                'images' => [
                    'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1605763240000-7e93b172d754?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category' => 'Western Dresses',
                'name' => 'A-Line Smocked Midi Dress',
                'price' => 1299.00,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'description' => 'Trendy square neck smocked midi dress in pastel hue. Elegant fit with comfortable waist stretch.',
                'sizes' => ['M' => 10, 'L' => 15, 'XL' => 8],
                'images' => [
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category' => 'Trendy Bottoms',
                'name' => 'High-Waist Wide Leg Cargo Trousers',
                'price' => 999.00,
                'discount_type' => 'none',
                'discount_value' => 0,
                'description' => 'Ultra-chic high-waist Korean baggy cargo pants with multi-utility pockets.',
                'sizes' => ['S' => 5, 'M' => 8, 'L' => 12],
                'images' => [
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            [
                'category' => 'Co-Ord Sets',
                'name' => 'Ribbed Knit Crop Top & Shorts Set',
                'price' => 1499.00,
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'description' => 'Cozy yet stylish matching 2-piece lounge set designed for modern comfort.',
                'sizes' => ['Free Size' => 25],
                'images' => [
                    'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85',
                ],
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category']];
            $images = $pData['images'];

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($pData['name'])],
                [
                    'category_id' => $cat->id,
                    'name' => $pData['name'],
                    'price' => $pData['price'],
                    'discount_type' => $pData['discount_type'],
                    'discount_value' => $pData['discount_value'],
                    'description' => $pData['description'],
                    'status' => 'active',
                ]
            );

            // Add sizes & stock
            foreach ($pData['sizes'] as $sizeName => $stockQty) {
                ProductSize::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $sizeName],
                    ['stock' => $stockQty]
                );
            }

            foreach ($images as $sortOrder => $imageUrl) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => $sortOrder],
                    ['image_path' => $imageUrl, 'is_primary' => $sortOrder === 0]
                );
            }
        }

        // 4. Seed Social Media
        SocialMedia::updateOrCreate(
            ['type' => 'whatsapp'],
            [
                'country_code' => '+91',
                'phone_number' => '8078037591',
                'status' => 'active',
                'sort_order' => 1,
            ]
        );

        SocialMedia::updateOrCreate(
            ['type' => 'instagram'],
            [
                'url' => 'https://instagram.com/quarawaldrop',
                'status' => 'active',
                'sort_order' => 2,
            ]
        );

        // 5. Seed Home Content
        HomeContent::updateOrCreate(
            ['title' => 'Welcome to QUARA WALDROP'],
            [
                'content_html' => '<div class="text-center py-4 bg-light rounded-4 my-4 p-4">
                    <h2 class="font-serif fw-bold text-dark mb-2" style="color: #C9962E;">AFFORDABLE WESTERN WEAR FOR EVERY OCCASION</h2>
                    <p class="lead text-muted max-w-700 mx-auto">Explore high quality Korean tops, western dresses, and statement co-ords curated specially for fashion-conscious ladies.</p>
                </div>',
                'image_position' => 'top',
                'status' => 'active',
            ]
        );
    }
}
