<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use App\Services\CartService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(Category $category, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'price' => 100,
            'status' => 'active',
        ]);
    }

    public function test_category_only_products_are_hidden_from_general_collections_but_remain_in_category(): void
    {
        $visible = Category::create(['name' => 'Everyday', 'slug' => 'everyday', 'status' => 'active', 'show_in_collection' => true]);
        $private = Category::create(['name' => 'Budget Finds', 'slug' => 'budget-finds', 'status' => 'active', 'show_in_collection' => false]);
        $publicProduct = $this->makeProduct($visible, 'Everyday Top');
        $categoryOnlyProduct = $this->makeProduct($private, 'Budget Top');

        $this->assertTrue(Product::visibleInCollection()->whereKey($publicProduct->id)->exists());
        $this->assertFalse(Product::visibleInCollection()->whereKey($categoryOnlyProduct->id)->exists());
        $this->assertTrue(Product::whereKey($categoryOnlyProduct->id)->whereHas('category', fn ($query) => $query->whereKey($private->id))->exists());
    }

    public function test_category_visibility_defaults_to_collection_and_can_be_changed_in_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.categories.create'))->assertOk()
            ->assertSee('Category and Product Collection')
            ->assertSee('Only inside this category');

        $this->post(route('admin.categories.store'), [
            'name' => 'Limited Collection',
            'text_color' => '#ffffff',
            'status' => 'active',
            'show_in_collection' => '0',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'limited-collection')->firstOrFail();
        $this->assertFalse($category->show_in_collection);

        $this->post(route('admin.categories.store'), [
            'name' => 'Standard Collection',
            'text_color' => '#ffffff',
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));
        $this->assertTrue(Category::where('slug', 'standard-collection')->firstOrFail()->show_in_collection);

        $this->put(route('admin.categories.update', $category), [
            'name' => 'Limited Collection',
            'text_color' => '#ffffff',
            'status' => 'active',
            'show_in_collection' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertTrue($category->fresh()->show_in_collection);
    }

    public function test_inactivating_category_hides_category_but_does_not_deactivate_its_products(): void
    {
        $category = Category::create([
            'name' => 'Seasonal',
            'slug' => 'seasonal',
            'status' => 'inactive',
            'show_in_collection' => true,
        ]);
        $product = $this->makeProduct($category, 'Seasonal Dress');

        $this->assertTrue(Product::active()->whereKey($product->id)->exists());
        $this->assertTrue(Product::active()->visibleInCollection()->whereKey($product->id)->exists());
        $this->assertFalse(Category::publicActive()->whereKey($category->id)->exists());
    }

    public function test_deactivating_category_detaches_products_and_they_remain_purchasable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Clearance', 'slug' => 'clearance', 'status' => 'active', 'show_in_collection' => true]);
        $product = $this->makeProduct($category, 'Clearance Dress');
        $product->categories()->sync([$category->id]);
        $size = ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock' => 4]);

        $this->postJson(route('admin.categories.toggle-status', $category))
            ->assertOk()
            ->assertJsonPath('status', 'inactive');

        $this->assertNull($product->fresh()->category_id);
        $this->assertSame(0, $category->products()->count());
        $this->assertSame('active', $product->fresh()->status);
        $this->assertTrue(Product::active()->visibleInCollection()->whereKey($product->id)->exists());
        $this->assertTrue(app(StockService::class)->checkStock($product->id, 'M', 1)['available']);
        $this->assertTrue(app(CartService::class)->add($product->id, 'M', 1)['success']);
    }

    public function test_deactivating_category_that_is_category_only_keeps_products_out_of_general_collection(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $category = Category::create(['name' => 'Private Picks', 'slug' => 'private-picks', 'status' => 'active', 'show_in_collection' => false]);
        $product = $this->makeProduct($category, 'Private Top');
        $product->categories()->sync([$category->id]);

        $this->postJson(route('admin.categories.toggle-status', $category))->assertOk();

        $this->assertNull($product->fresh()->category_id);
        $this->assertFalse($product->fresh()->collection_visible);
        $this->assertFalse(Product::active()->visibleInCollection()->whereKey($product->id)->exists());
    }

    public function test_admin_can_add_product_to_an_additional_category_without_page_navigation(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $originalCategory = Category::create(['name' => 'Original', 'slug' => 'original', 'status' => 'active', 'show_in_collection' => true]);
        $targetCategory = Category::create(['name' => 'Target', 'slug' => 'target', 'status' => 'active', 'show_in_collection' => true]);
        $product = $this->makeProduct($originalCategory, 'Shared Product');
        $product->categories()->sync([$originalCategory->id]);

        $this->postJson(route('admin.categories.products.attach', [$targetCategory, $product]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($targetCategory->products()->whereKey($product->id)->exists());
        $this->assertSame($originalCategory->id, $product->fresh()->category_id);
    }

    public function test_admin_category_list_loads_more_categories_as_json_for_scrolling(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (range(1, 11) as $number) {
            Category::create([
                'name' => 'Category ' . $number,
                'slug' => 'category-' . $number,
                'status' => 'active',
            ]);
        }

        $response = $this->get(route('admin.categories.index', ['ajax' => 1]))
            ->assertOk()
            ->assertJsonPath('has_more', true);
        $this->assertStringContainsString('page=2', $response->json('next_page_url'));
    }
}
