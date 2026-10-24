<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoldOutProductsLastTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, int $stock): Product
    {
        $category = Category::firstOrCreate(['slug' => 'sorting'], ['name' => 'Sorting', 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => $name, 'price' => 100, 'status' => 'active']);
        ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock' => $stock]);

        return $product->fresh();
    }

    private function book(Product $product, int $quantity, string $status = 'active', int $sold = 0): void
    {
        ProductBooking::create([
            'product_id' => $product->id, 'product_size_id' => $product->sizes()->first()->id,
            'quantity' => $quantity, 'sold_quantity' => $sold, 'booked_by' => 'Customer',
            'booking_type' => 'whatsapp_customer', 'booking_date' => now()->toDateString(), 'status' => $status,
        ]);
    }

    public function test_products_shown_as_sold_out_sort_after_available_ones(): void
    {
        // Created first so a plain id order would list them first.
        $fullyBooked = $this->product('Fully Booked', 1);
        $this->book($fullyBooked, 1);
        $noStock = $this->product('No Stock', 0);
        $partlyBooked = $this->product('Partly Booked', 2);
        $this->book($partlyBooked, 1);
        $released = $this->product('Booking Released', 1);
        $this->book($released, 1, 'released');
        $available = $this->product('Available', 3);

        $order = Product::inStockFirst()->orderBy('id')->pluck('name')->all();

        $this->assertSame(['Partly Booked', 'Booking Released', 'Available', 'Fully Booked', 'No Stock'], $order);
        $this->assertSame(0, $fullyBooked->fresh()->available_total_stock, 'The shop shows it SOLD OUT.');
    }

    public function test_home_new_arrivals_show_sold_out_products_last_even_when_newest(): void
    {
        \App\Models\HomePageSection::updateOrCreate(['section_key' => 'new_arrivals'], ['enabled' => true, 'sort_order' => 1, 'items_to_show' => 8]);
        $older = $this->product('Older Available', 2);
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newestBookedOut = $this->product('Newest Booked Out', 1);
        $this->book($newestBookedOut, 1);

        $this->get(route('home'))->assertOk()->assertViewHas('sectionProducts',
            fn ($sections) => $sections['new_arrivals']->pluck('name')->all() === ['Older Available', 'Newest Booked Out']);
    }
}
