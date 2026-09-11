<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSizeSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_forms_include_size_suggestions(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $category = Category::create(['name' => 'Kurtis', 'slug' => 'kurtis', 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Cotton Kurti', 'price' => 500, 'status' => 'active']);
        ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock' => 3, 'chest' => '38', 'waist' => '34', 'length' => '42']);

        foreach ([route('admin.products.create'), route('admin.products.edit', $product)] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Magic Size Suggestion')
                ->assertSee('js/product-size-suggestion.js')
                ->assertSee('Full circumference (inches)')
                ->assertSee('Flat width (inches; double for suggestion)');
        }
    }
}
