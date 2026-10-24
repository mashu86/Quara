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
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 21 && $bookings->count() === 20);
        $this->get(route('admin.products.booked', ['page' => 2]))->assertOk()->assertSee('Booked Dress 1');
        $this->get(route('admin.products.booked', ['search' => 'Customer 21']))->assertOk()
            ->assertSee('Booked Dress 21')->assertDontSee('Booked Dress 20')
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 1);
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

    /** ABC has 2 pieces of size L; customers C and D each book one. */
    private function twoBookingsOfOneSize(): array
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $product = $this->product('ABC Kurti', false, null);
        $size = ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock' => 2]);
        $book = fn (string $who) => \App\Models\ProductBooking::create([
            'product_id' => $product->id, 'product_size_id' => $size->id, 'quantity' => 1,
            'booked_by' => $who, 'booking_type' => 'whatsapp_customer', 'booking_date' => now()->toDateString(), 'status' => 'active',
        ]);

        return [$size, $book('Customer C'), $book('Customer D')];
    }

    public function test_each_size_booking_is_its_own_row(): void
    {
        [$size] = $this->twoBookingsOfOneSize();
        $this->assertEquals(0, $size->fresh()->available_stock);

        $this->get(route('admin.products.booked'))->assertOk()
            ->assertSee('Customer C')->assertSee('Customer D')
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 2
                && $bookings->every(fn ($row) => $row->type === 'size' && $row->size->size === 'L' && $row->quantity === 1));
        $this->get(route('admin.products.booked', ['search' => 'Customer D']))
            ->assertSee('Customer D')->assertDontSee('Customer C');
    }

    public function test_unbooking_one_customer_keeps_the_other_booking_and_frees_one_piece(): void
    {
        [$size, $bookingC, $bookingD] = $this->twoBookingsOfOneSize();

        $this->postJson(route('admin.products.bookings.release', $bookingC))->assertOk();

        $this->assertModelMissing($bookingC);
        $this->assertEquals('active', $bookingD->fresh()->status);
        $this->assertEquals(1, $size->fresh()->available_stock, 'C\'s piece is available again.');
        $this->assertEquals(2, $size->fresh()->stock);
        $this->get(route('admin.products.booked'))->assertSee('Customer D')->assertDontSee('Customer C');
    }

    public function test_bulk_unbook_releases_size_and_whole_product_bookings(): void
    {
        [$size, $bookingC, $bookingD] = $this->twoBookingsOfOneSize();
        $wholeProduct = $this->product('Whole Booking Dress', true, 'Customer E');

        $this->post(route('admin.products.booked.bulk-unbook'), [
            'booking_ids' => [$bookingC->id], 'product_ids' => [$wholeProduct->id],
        ])->assertRedirect()->assertSessionHas('success', '2 booking(s) unbooked successfully.');

        $this->assertModelMissing($bookingC);
        $this->assertEquals('active', $bookingD->fresh()->status);
        $this->assertFalse($wholeProduct->fresh()->is_out_of_stock);
        $this->assertEquals(1, $size->fresh()->available_stock);
    }

    public function test_product_edit_shows_booked_items_on_the_size_row(): void
    {
        [$size, $bookingC] = $this->twoBookingsOfOneSize();

        $this->get(route('admin.products.edit', $size->product_id))->assertOk()
            ->assertSee('2 item(s) booked')->assertSee('Customer C × 1')->assertSee('0 available');
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
