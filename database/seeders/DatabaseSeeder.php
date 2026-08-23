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
                'email' => 'quarawaldrop@gmail.com',
                'phone' => '9876543210',
                'password' => Hash::make('Quara86'),
                'role' => 'admin',
            ]
        );

        // 2. Seed Social Media
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
