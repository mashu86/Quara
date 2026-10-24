<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CategoryComboEligibilityTest extends TestCase
{
    use RefreshDatabase;

    private function comboCategory(string $slug, int $minimum = 2): Category
    {
        return Category::create([
            'name' => ucfirst($slug), 'slug' => $slug, 'status' => 'active',
            'is_offer_category' => true, 'offer_type' => 'combo', 'is_combo_offer' => true,
            'is_active_offer' => true, 'min_count' => $minimum, 'combo_price' => 500,
            'allow_pre_min_purchase' => true, 'pre_min_purchase_offer_price' => true,
            'delivery_charge_mode' => 'free', 'delivery_charge' => 0,
        ]);
    }

    private function product(Category $category, string $name, float $price): Product
    {
        $product = Product::create([
            'category_id' => $category->id, 'combo_category_id' => $category->id,
            'name' => $name, 'price' => $price, 'final_price' => $price, 'status' => 'active',
        ]);
        ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock' => 20]);
        return $product;
    }

    private function discountCategory(string $slug, float $discountValue = 20): Category
    {
        return Category::create([
            'name' => ucfirst($slug), 'slug' => $slug, 'status' => 'active',
            'is_offer_category' => true, 'offer_type' => 'discount', 'is_combo_offer' => false,
            'is_active_offer' => true, 'discount_type' => 'percentage', 'discount_value' => $discountValue,
        ]);
    }

    public function test_one_eligible_item_gets_no_combo_discount_even_when_pre_minimum_offer_price_is_enabled(): void
    {
        $category = $this->comboCategory('sweaters');
        $product = $this->product($category, 'Sweater', 269);

        $this->assertTrue(app(CartService::class)->add($product->id, 'M', 1)['success']);
        $item = array_values(Session::get('cart'))[0];

        $this->assertSame(269.0, $item['final_price']);
        $this->assertEquals(0.0, $item['discount_amount']);
        $this->assertFalse($item['is_combo_offer'] ?? false);
    }

    public function test_mixed_categories_do_not_count_toward_combo_minimum_or_receive_its_price(): void
    {
        $combo = $this->comboCategory('sweaters');
        $other = Category::create(['name' => 'Tops', 'slug' => 'tops', 'status' => 'active']);
        $sweater = $this->product($combo, 'Sweater', 269);
        $top = $this->product($other, 'Top', 139);
        $cart = app(CartService::class);

        $cart->add($sweater->id, 'M', 1);
        $cart->add($top->id, 'M', 1);
        $items = collect(Session::get('cart'))->keyBy('product_id');

        $this->assertSame(269.0, $items[$sweater->id]['final_price']);
        $this->assertSame(139.0, $items[$top->id]['final_price']);
        $this->assertFalse($items[$sweater->id]['is_combo_offer'] ?? false);
        $this->assertFalse($items[$top->id]['is_combo_offer'] ?? false);
    }

    public function test_two_items_in_a_five_item_combo_keep_their_individual_prices(): void
    {
        $category = $this->comboCategory('five-item-combo', 5);
        $first = $this->product($category, 'Plaid Top', 189);
        $second = $this->product($category, 'Olive Top', 169);
        $cart = app(CartService::class);

        $cart->add($first->id, 'M', 1);
        $cart->add($second->id, 'M', 1);
        $items = collect(Session::get('cart'))->keyBy('product_id');

        $this->assertSame(189.0, $items[$first->id]['final_price']);
        $this->assertSame(169.0, $items[$second->id]['final_price']);
        $this->assertEquals(0, (float) $items[$first->id]['discount_amount']);
        $this->assertEquals(0, (float) $items[$second->id]['discount_amount']);
    }

    public function test_combo_applies_only_after_two_products_from_its_category_are_in_cart(): void
    {
        $category = $this->comboCategory('sweaters');
        $first = $this->product($category, 'Sweater One', 300);
        $second = $this->product($category, 'Sweater Two', 300);
        $cart = app(CartService::class);

        $cart->add($first->id, 'M', 1);
        $cart->add($second->id, 'M', 1);
        $items = collect(Session::get('cart'))->keyBy('product_id');

        $this->assertSame(250.0, $items[$first->id]['final_price']);
        $this->assertSame(250.0, $items[$second->id]['final_price']);
        $this->assertTrue($items[$first->id]['is_combo_offer']);
        $this->assertTrue($items[$second->id]['is_combo_offer']);
    }

    public function test_removing_an_eligible_item_reverts_the_remaining_item_to_its_normal_price(): void
    {
        $category = $this->comboCategory('sweaters');
        $first = $this->product($category, 'Sweater One', 300);
        $second = $this->product($category, 'Sweater Two', 300);
        $cart = app(CartService::class);
        $cart->add($first->id, 'M', 1);
        $cart->add($second->id, 'M', 1);

        $cart->remove($first->id . '_M');
        $remaining = array_values(Session::get('cart'))[0];

        $this->assertSame(300.0, $remaining['final_price']);
        $this->assertFalse($remaining['is_combo_offer'] ?? false);
    }

    public function test_eligible_quantities_above_minimum_keep_the_configured_per_item_combo_rate(): void
    {
        $category = $this->comboCategory('sweaters');
        $product = $this->product($category, 'Sweater', 300);

        app(CartService::class)->add($product->id, 'M', 3);
        $item = array_values(Session::get('cart'))[0];

        $this->assertSame(250.0, $item['final_price']);
        $this->assertSame(750.0, $item['subtotal']);
    }

    public function test_combo_builder_rejects_products_outside_the_configured_category(): void
    {
        $combo = $this->comboCategory('sweaters');
        $other = Category::create(['name' => 'Tops', 'slug' => 'tops', 'status' => 'active']);
        $top = $this->product($other, 'Top', 139);

        $result = app(CartService::class)->addComboItems([
            ['product_id' => $top->id, 'size' => 'M', 'quantity' => 2],
        ], $combo);

        $this->assertFalse($result['success']);
        $this->assertSame([], Session::get('cart', []));
    }

    public function test_discount_offer_item_does_not_fill_a_combo_minimum(): void
    {
        $combo = $this->comboCategory('three-for-199', 3);
        $combo->update(['combo_price' => 199]);
        $discount = $this->discountCategory('twenty-percent-off');
        $first = $this->product($combo, 'Combo Item One', 300);
        $second = $this->product($combo, 'Combo Item Two', 300);
        $discounted = $this->product($discount, 'Discount Item', 100);
        $cart = app(CartService::class);

        $cart->add($first->id, 'M', 1);
        $cart->add($second->id, 'M', 1);
        $cart->add($discounted->id, 'M', 1);
        $items = collect($cart->getCart())->keyBy('product_id');

        $this->assertSame(300.0, $items[$first->id]['final_price']);
        $this->assertSame(300.0, $items[$second->id]['final_price']);
        $this->assertSame(80.0, $items[$discounted->id]['final_price']);
        $this->assertFalse($items[$first->id]['is_combo_offer']);
        $this->assertFalse($items[$discounted->id]['is_combo_offer']);
    }

    public function test_discount_and_normal_items_do_not_change_a_qualified_combo_total(): void
    {
        $combo = $this->comboCategory('three-for-199', 3);
        $combo->update(['combo_price' => 199]);
        $discount = $this->discountCategory('twenty-percent-off');
        $normal = Category::create(['name' => 'Basics', 'slug' => 'basics', 'status' => 'active']);
        $comboItems = [
            $this->product($combo, 'Combo Item One', 300),
            $this->product($combo, 'Combo Item Two', 300),
            $this->product($combo, 'Combo Item Three', 300),
        ];
        $discounted = $this->product($discount, 'Discount Item', 100);
        $regular = $this->product($normal, 'Regular Item', 50);
        $cart = app(CartService::class);

        foreach ($comboItems as $product) $cart->add($product->id, 'M', 1);
        $cart->add($discounted->id, 'M', 1);
        $cart->add($regular->id, 'M', 1);
        $items = collect($cart->getCart())->keyBy('product_id');

        $this->assertSame(199.0, round(collect($comboItems)->sum(fn ($product) => $items[$product->id]['subtotal']), 2));
        $this->assertSame(80.0, $items[$discounted->id]['final_price']);
        $this->assertSame(50.0, $items[$regular->id]['final_price']);
        $this->assertTrue($items[$comboItems[0]->id]['is_combo_offer']);
        $this->assertFalse($items[$discounted->id]['is_combo_offer']);
        $this->assertFalse($items[$regular->id]['is_combo_offer']);
    }

    public function test_selected_checkout_items_are_repriced_against_only_the_selected_combo_quantity(): void
    {
        $category = $this->comboCategory('three-for-199', 3);
        $category->update(['combo_price' => 199]);
        $products = [
            $this->product($category, 'Combo Item One', 300),
            $this->product($category, 'Combo Item Two', 300),
            $this->product($category, 'Combo Item Three', 300),
        ];
        $cart = app(CartService::class);
        foreach ($products as $product) $cart->add($product->id, 'M', 1);

        $allCartItems = Session::get('cart');
        $selectedItems = array_slice($allCartItems, 0, 2, true);
        Session::put('cart', $selectedItems);
        $selectedCart = collect($cart->getCart())->keyBy('product_id');

        $this->assertCount(2, $selectedCart);
        foreach (array_slice($products, 0, 2) as $product) {
            $this->assertSame(300.0, $selectedCart[$product->id]['final_price']);
            $this->assertFalse($selectedCart[$product->id]['is_combo_offer']);
        }
    }

    public function test_quantities_from_separate_combo_categories_are_not_pooled(): void
    {
        $firstCombo = $this->comboCategory('combo-one', 2);
        $secondCombo = $this->comboCategory('combo-two', 2);
        $first = $this->product($firstCombo, 'First Combo Product', 300);
        $second = $this->product($secondCombo, 'Second Combo Product', 300);
        $cart = app(CartService::class);

        $cart->add($first->id, 'M', 1);
        $cart->add($second->id, 'M', 1);
        $items = collect($cart->getCart())->keyBy('product_id');

        $this->assertSame(300.0, $items[$first->id]['final_price']);
        $this->assertSame(300.0, $items[$second->id]['final_price']);
        $this->assertFalse($items[$first->id]['is_combo_offer']);
        $this->assertFalse($items[$second->id]['is_combo_offer']);
    }
}
