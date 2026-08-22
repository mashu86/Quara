<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'Quara'],
            [
                'name' => 'Quara Admin',
                'email' => 'quarawaldrop@gmail.com',
                'phone' => '+918078037591',
                'role' => 'admin',
                'password' => Hash::make('Quara86'),
            ]
        );
    }
}
