<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestockClearsSoldOutTest extends TestCase
{
    use RefreshDatabase;

    private function soldOutProduct(array $overrides = []): array
    {
        $category = Category::create(['name' => 'Restock', 'slug' => 'restock', 'status' => 'active']);
        // The last piece sold online: stock 0 and the product flagged sold out.
        $product = Product::create(array_merge([
            'category_id' => $category->id, 'name' => 'Restocked Kurti', 'price' => 1000,
            'status' => 'active', 'is_out_of_stock' => true,
        ], $overrides));
        $size = ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock' => 0]);

        return [$product, $size];
    }

    public function test_adding_stock_to_a_sold_out_product_makes_it_available_and_bookable_again(): void
    {
        [$product, $size] = $this->soldOutProduct();

        app(StockService::class)->adjustStock($size->id, 2, 'Restock', 'Admin');

        $this->assertFalse($product->fresh()->is_out_of_stock);
        $this->assertEquals(2, $product->fresh()->available_total_stock);
    }

    public function test_setting_stock_to_zero_marks_the_product_sold_out(): void
    {
        [$product, $size] = $this->soldOutProduct(['is_out_of_stock' => false]);
        $size->update(['stock' => 1]);

        app(StockService::class)->adjustStock($size->id, 0, 'Correction', 'Admin');

        $this->assertTrue($product->fresh()->is_out_of_stock);
    }

    public function test_restocking_keeps_a_whole_product_booking(): void
    {
        [$product, $size] = $this->soldOutProduct(['booked_by' => 'Customer C', 'booking_type' => 'whatsapp_customer']);

        app(StockService::class)->adjustStock($size->id, 2, 'Restock', 'Admin');

        $this->assertTrue($product->fresh()->is_out_of_stock, 'The booking still holds the product.');
        $this->assertEquals('Customer C', $product->fresh()->booked_by);
    }
}
