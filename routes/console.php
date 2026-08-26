<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:clear-test-data', function () {
    $this->info('Starting test data wipe...');

    Schema::disableForeignKeyConstraints();

    DB::table('order_items')->truncate();
    DB::table('payments')->truncate();
    DB::table('orders')->truncate();
    DB::table('expenses')->truncate();
    DB::table('notifications')->truncate();
    DB::table('stock_movements')->truncate();
    DB::table('product_sizes')->truncate();
    DB::table('product_images')->truncate();
    if (Schema::hasTable('category_product')) {
        DB::table('category_product')->truncate();
    }
    DB::table('products')->truncate();
    DB::table('categories')->truncate();

    Schema::enableForeignKeyConstraints();

    $this->info('Successfully cleared categories, products, orders, payments, expenses, stock movements, and notifications!');
    $this->info('Preserved: Users (Admin), Settings, Social Media, and Home Content.');
})->purpose('Wipe all test data (categories, products, orders, expenses) for production readiness');
