<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    public function run(): void
    {
        $socials = [
            [
                'type' => 'whatsapp',
                'country_code' => '+91',
                'phone_number' => '8078037591',
                'url' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'type' => 'instagram',
                'country_code' => '+91',
                'phone_number' => null,
                'url' => 'https://instagram.com/quarawaldrop.official',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'type' => 'facebook',
                'country_code' => '+91',
                'phone_number' => null,
                'url' => 'https://facebook.com/quarawaldrop.official',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'type' => 'youtube',
                'country_code' => '+91',
                'phone_number' => null,
                'url' => 'https://youtube.com/@quarawaldrop',
                'status' => 'active',
                'sort_order' => 4,
            ],
        ];

        foreach ($socials as $soc) {
            SocialMedia::updateOrCreate(['type' => $soc['type']], $soc);
        }
    }
}
