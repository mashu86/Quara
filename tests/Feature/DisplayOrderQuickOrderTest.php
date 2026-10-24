<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayOrderQuickOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_order_page_offers_fastest_display_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.display-order.index'))->assertOk()
            ->assertSee('Fastest Display Order')->assertSee('id="quickOrderModal"', false);
    }

    public function test_saved_order_puts_the_tapped_products_first(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $category = Category::create(['name' => 'Tops', 'slug' => 'tops', 'status' => 'active']);
        [$a, $b, $c] = collect(['A', 'B', 'C'])->map(fn ($name) => Product::create([
            'category_id' => $category->id, 'name' => $name, 'price' => 100, 'status' => 'active',
        ]))->all();

        // The modal sends the tapped products (C, then A) followed by the rest.
        $this->postJson(route('admin.display-order.update-product-order'), ['order' => [$c->id, $a->id, $b->id]])->assertOk();

        $this->assertSame(['C', 'A', 'B'], Product::orderBy('sort_order')->pluck('name')->all());
    }
}
