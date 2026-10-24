<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductSize;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvertLegacyBookingsTest extends TestCase
{
    use RefreshDatabase;

    /** An old-style booking: the whole product locked for one customer. */
    private function legacyBooked(string $code, array $stockBySize): Product
    {
        $category = Category::firstOrCreate(['slug' => 'legacy'], ['name' => 'Legacy', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Dress '.$code, 'product_code' => $code, 'price' => 1000,
            'status' => 'active', 'is_out_of_stock' => true, 'booked_by' => 'Mehrin',
            'booking_type' => 'instagram_customer', 'booking_date' => '2026-10-01',
        ]);
        foreach ($stockBySize as $size => $stock) {
            ProductSize::create(['product_id' => $product->id, 'size' => $size, 'stock' => $stock]);
        }

        return $product;
    }

    public function test_single_size_booking_becomes_one_piece_and_new_stock_stays_free(): void
    {
        $product = $this->legacyBooked('PR723', ['S' => 3]);

        $this->artisan('bookings:convert-legacy')->assertSuccessful();

        $booking = ProductBooking::sole();
        $size = $product->sizes()->first();
        $this->assertEquals([$size->id, 1, 'Mehrin', 'instagram_customer', '2026-10-01'],
            [$booking->product_size_id, $booking->quantity, $booking->booked_by, $booking->booking_type, $booking->booking_date->toDateString()]);
        $product->refresh();
        $this->assertFalse($product->is_out_of_stock);
        $this->assertNull($product->booked_by);
        $this->assertEquals(2, $size->fresh()->available_stock, '1 booked, 2 free to sell or book.');

        // Adding 2 more pieces later does not go to the old booking.
        app(StockService::class)->adjustStock($size->id, 5, 'New stock', 'Admin');
        $this->assertEquals(4, $size->fresh()->available_stock);
    }

    public function test_the_only_size_in_stock_is_taken_as_the_booked_size(): void
    {
        $product = $this->legacyBooked('PR500', ['S' => 0, 'L' => 1, 'XL' => 0]);

        $this->artisan('bookings:convert-legacy')->assertSuccessful();

        $this->assertEquals('L', ProductBooking::sole()->productSize->size);
        $this->assertEquals(0, $product->sizes()->where('size', 'L')->first()->available_stock);
    }

    public function test_unclear_size_is_left_unchanged_and_dry_run_changes_nothing(): void
    {
        $unclear = $this->legacyBooked('PR600', ['M' => 1, 'L' => 2]);
        $clear = $this->legacyBooked('PR601', ['M' => 1]);

        $this->artisan('bookings:convert-legacy --dry-run')->assertSuccessful();
        $this->assertDatabaseCount('product_bookings', 0);
        $this->assertTrue($clear->fresh()->is_out_of_stock);

        $this->artisan('bookings:convert-legacy')->assertSuccessful();
        $this->assertEquals($clear->id, ProductBooking::sole()->product_id);
        $this->assertTrue($unclear->fresh()->is_out_of_stock, 'Still a whole-product booking.');
        $this->assertEquals('Mehrin', $unclear->fresh()->booked_by);
    }
}
