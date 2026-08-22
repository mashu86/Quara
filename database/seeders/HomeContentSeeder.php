<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        HomeContent::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Trendy & Affordable Western Wear',
                'content_html' => '<div class="qw-hero-banner text-center py-5 px-3">
    <h1 class="qw-brand-title display-4 font-serif font-bold gold-gradient-text mb-3">DRESS BEYOND ORDINARY</h1>
    <p class="lead text-dark max-w-2xl mx-auto font-sans mb-4">Discover high-fashion Korean tops, flattering western dresses, and affordable everyday chic fashion crafted for the modern woman.</p>
    <div class="d-flex justify-content-center gap-3">
        <a href="/shop" class="btn btn-gold btn-lg px-4 shadow-sm rounded-pill fw-semibold">EXPLORE NEW ARRIVALS</a>
        <a href="/category/korean-tops" class="btn btn-outline-dark btn-lg px-4 rounded-pill fw-semibold">KOREAN COLLECTION</a>
    </div>
</div>',
                'custom_css' => '.qw-brand-title { letter-spacing: 2px; text-transform: uppercase; }
.gold-gradient-text { background: linear-gradient(135deg, #C9962E 0%, #D8AD4A 50%, #9A6A12 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.btn-gold { background-color: #C9962E; color: #FFFFFF; border: none; transition: all 0.3s ease; }
.btn-gold:hover { background-color: #9A6A12; color: #FFFFFF; transform: translateY(-2px); boxShadow: 0 8px 20px rgba(201,150,46,0.3); }',
                'image_position' => 'top',
                'status' => 'active',
            ]
        );
    }
}
