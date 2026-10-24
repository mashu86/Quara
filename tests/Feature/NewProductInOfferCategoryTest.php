<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewProductInOfferCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function offerCategory(): Category
    {
        return Category::create([
            'name' => 'Festive 20% Off', 'slug' => 'festive-20-off', 'status' => 'active',
            'is_offer_category' => true, 'offer_type' => 'discount', 'is_active_offer' => true,
            'discount_type' => 'percentage', 'discount_value' => 20,
        ]);
    }

    private function storeProduct(array $fields): Product
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.products.store'), array_merge([
                'name' => 'New Kurti', 'price' => 1000, 'status' => 'active',
                'main_image' => UploadedFile::fake()->image('kurti.jpg'),
                'sizes' => ['L'], 'stocks' => [1],
            ], $fields))->assertSessionHasNoErrors()->assertRedirect();

        return Product::with('categories')->latest('id')->firstOrFail();
    }

    /** @dataProvider ways_to_pick_the_offer_category */
    public function test_new_product_added_to_an_offer_category_gets_its_discount(string $how): void
    {
        $offer = $this->offerCategory();
        $product = $this->storeProduct($how === 'checkbox'
            ? ['category_ids' => [$offer->id]]
            : ['combo_category_id' => $offer->id]);

        $this->assertEquals(800, (float) $product->final_price);
        $this->assertEquals(800, $product->effective_final_price);
        $this->assertEquals($offer->id, $product->combo_category_id);
        $this->assertEquals([$offer->id], $product->categories->pluck('id')->all(), 'Listed under the offer category like its other products.');
    }

    public function test_offer_price_applies_only_while_the_offer_is_on(): void
    {
        $offer = $this->offerCategory();
        $product = $this->storeProduct(['category_ids' => [$offer->id]]);

        $offer->update(['is_active_offer' => false, 'status' => 'inactive']);
        $this->assertEquals(1000, (float) $product->fresh()->final_price, 'Shown price drops the discount.');
        $this->assertEquals(1000, $product->fresh()->effective_final_price, 'Cart charges the full price.');

        $offer->update(['is_active_offer' => true, 'status' => 'active']);
        $this->assertEquals(800, (float) $product->fresh()->final_price);
        $this->assertEquals(800, $product->fresh()->effective_final_price);
    }

    public function test_product_added_to_a_switched_off_offer_gets_no_discount(): void
    {
        $offer = $this->offerCategory();
        $offer->update(['is_active_offer' => false, 'status' => 'inactive']);
        $product = $this->storeProduct(['category_ids' => [$offer->id]]);

        $this->assertEquals(1000, (float) $product->final_price);
        $this->assertEquals(1000, $product->effective_final_price);

        $offer->update(['is_active_offer' => true, 'status' => 'active']);
        $this->assertEquals(800, (float) $product->fresh()->final_price, 'It joins the discount once the offer is switched on.');
    }

    public function test_admin_offer_toggle_reprices_the_offer_products(): void
    {
        $offer = $this->offerCategory();
        $product = $this->storeProduct(['category_ids' => [$offer->id]]);

        $this->post(route('admin.offer-sale.activate'), ['offer_category_id' => $offer->id]);
        $this->assertFalse($offer->fresh()->is_active_offer);
        $this->assertEquals(1000, (float) $product->fresh()->final_price);

        $this->post(route('admin.offer-sale.activate'), ['offer_category_id' => $offer->id]);
        $this->assertEquals(800, (float) $product->fresh()->final_price);

        // "Offer Store OFF" switches every offer off in one bulk update.
        $this->post(route('admin.offer-sale.activate'), ['offer_category_id' => 0]);
        $this->assertEquals(1000, (float) $product->fresh()->final_price);
    }

    public static function ways_to_pick_the_offer_category(): array
    {
        return ['category checkbox' => ['checkbox'], 'offer dropdown' => ['dropdown']];
    }
}
