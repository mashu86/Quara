<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookedProductsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, bool $booked, ?string $bookedBy): Product
    {
        $category = Category::firstOrCreate(['slug' => 'booking-tests'], [
            'name' => 'Booking Tests', 'status' => 'active',
        ]);

        return Product::create([
            'category_id' => $category->id, 'name' => $name, 'price' => 1000,
            'status' => 'active', 'is_out_of_stock' => $booked, 'booked_by' => $bookedBy,
        ]);
    }

    public function test_list_shows_current_bookings_and_supports_search_and_pagination(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->product('Sold Out Item', true, null);
        $this->product('Available Item', false, 'Old Booking');
        $this->product('Blank Booking Item', true, '   ');
        for ($i = 1; $i <= 21; $i++) {
            $this->product('Booked Dress '.$i, true, 'Customer '.$i);
        }

        $this->get(route('admin.products.booked'))->assertOk()
            ->assertSee('Booked Dress 21')->assertSee('Customer 21')
            ->assertDontSee('Sold Out Item')->assertDontSee('Available Item')
            ->assertDontSee('Blank Booking Item')
            ->assertViewHas('products', fn ($products) => $products->total() === 21 && $products->count() === 20);
        $this->get(route('admin.products.booked', ['page' => 2]))->assertOk()->assertSee('Booked Dress 1');
        $this->get(route('admin.products.booked', ['search' => 'Customer 21']))->assertOk()
            ->assertSee('Booked Dress 21')->assertDontSee('Booked Dress 20')
            ->assertViewHas('products', fn ($products) => $products->total() === 1);
    }

    public function test_unbook_uses_product_master_action_without_changing_inventory(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $product = $this->product('Reserved Dress', true, 'Anjali');
        $size = ProductSize::create([
            'product_id' => $product->id, 'size' => 'M', 'stock' => 3, 'reserved_stock' => 1,
        ]);

        $this->from(route('admin.products.booked'))
            ->post(route('admin.products.toggle-out-of-stock', $product), [
                'is_out_of_stock' => '0', 'booked_by' => '',
            ])->assertRedirect(route('admin.products.booked'))->assertSessionHas('success');

        $this->assertFalse($product->fresh()->is_out_of_stock);
        $this->assertNull($product->fresh()->booked_by);
        $this->assertEquals(3, $size->fresh()->stock);
        $this->assertEquals(1, $size->fresh()->reserved_stock);
        $this->get(route('admin.products.booked'))->assertDontSee('Reserved Dress');
        $this->get(route('admin.products.index'))->assertOk()->assertSee('Reserved Dress');
    }

    public function test_guest_cannot_view_bookings_or_unbook_products(): void
    {
        $product = $this->product('Private Booking', true, 'Customer');
        $this->get(route('admin.products.booked'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.products.toggle-out-of-stock', $product), [
            'is_out_of_stock' => '0',
        ])->assertRedirect(route('admin.login'));
        $this->assertTrue($product->fresh()->is_out_of_stock);
    }
}
