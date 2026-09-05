<?php

return [
    'timezone' => 'Asia/Kolkata',
    'successful_order_statuses' => ['confirmed', 'processing', 'packed', 'shipped', 'delivered'],
    'successful_payment_statuses' => ['paid', 'completed'],
    'normal_weight' => 100,
    'return_weight' => 50,
    'return_operation_types' => ['product_returned', 'customer_return', 'product_exchange', 'wrong_product_sent', 'product_damaged'],
    'excluded_test_phones' => ['9544832975'],
    // Temporary drafts use a lock-capable cache, never the permanent draw tables.
    // For multiple application servers, configure a shared Redis cache store.
    'cache_store' => env('LUCKYWINNER_CACHE_STORE', 'file'),
    'draft_lifetime_hours' => 24,
];
