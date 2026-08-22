<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Korean Tops',
                'slug' => 'korean-tops',
                'background_image' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=1200&q=85',
                'text_color' => '#FFFFFF',
                'status' => 'active',
            ],
            [
                'name' => 'Western Dresses',
                'slug' => 'western-dresses',
                'background_image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=1200&q=85',
                'text_color' => '#FFFFFF',
                'status' => 'active',
            ],
            [
                'name' => 'Casual Tops',
                'slug' => 'casual-tops',
                'background_image' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1200&q=85',
                'text_color' => '#FFFFFF',
                'status' => 'active',
            ],
            [
                'name' => 'Party Wear',
                'slug' => 'party-wear',
                'background_image' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=1200&q=85',
                'text_color' => '#FFFFFF',
                'status' => 'active',
            ],
            [
                'name' => 'Trending Bottoms',
                'slug' => 'trending-bottoms',
                'background_image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=1200&q=85',
                'text_color' => '#FFFFFF',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
