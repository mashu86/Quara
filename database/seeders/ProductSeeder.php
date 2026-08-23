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
        $productsData = [];

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
