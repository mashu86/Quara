<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatePaidProductsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_customer_repeated_paid_orders_are_duplicates_but_returns_and_self_test_orders_are_excluded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Report Category', 'slug' => 'report-category', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Report Test Shirt', 'slug' => 'report-test-shirt',
            'price' => 169, 'final_price' => 169, 'status' => 'active',
        ]);

        $makeItem = function (string $number, string $phone = '9876543210') use ($product): OrderItem {
            $order = Order::create([
                'order_number' => $number, 'customer_name' => 'Report Customer', 'customer_phone' => $phone,
                'house_building' => 'House', 'street' => 'Street', 'area' => 'Area', 'city' => 'Kochi',
                'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => '682001',
                'subtotal' => 169, 'grand_total' => 169, 'payment_method' => 'online',
                'payment_status' => 'paid', 'order_status' => 'delivered',
            ]);
            return OrderItem::create([
                'order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name,
                'size' => 'M', 'unit_price' => 169, 'final_unit_price' => 169,
                'quantity' => 1, 'subtotal' => 169, 'item_status' => 'active',
            ]);
        };

        $returnedItem = $makeItem('RETURNED-ORDER', '9876500001');
        OrderOperation::create([
            'order_id' => $returnedItem->order_id, 'order_item_id' => $returnedItem->id,
            'product_id' => $product->id, 'operation_type' => 'product_returned', 'status' => 'active',
        ]);
        $makeItem('PAID-ORDER-1', '9876500002');
        $makeItem('SELF-TEST-ORDER', '+91 95448-32975');

        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products', ['tab' => 'legacy']));
        $response->assertOk()->assertDontSee('Report Test Shirt')->assertDontSee('RETURNED-ORDER')->assertDontSee('SELF-TEST-ORDER');

        $makeItem('PAID-ORDER-2-SAME-BUYER', '+91 98765 00002');
        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products', ['tab' => 'legacy']));
        $response->assertOk()->assertSee('Report Test Shirt')->assertSee('PAID-ORDER-1')->assertSee('PAID-ORDER-2-SAME-BUYER')
            ->assertDontSee('RETURNED-ORDER')->assertDontSee('SELF-TEST-ORDER');
    }

    public function test_oversold_tab_flags_sizes_where_paid_plus_booked_exceeds_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Oversold Category', 'slug' => 'oversold-category', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Oversold Test Top', 'slug' => 'oversold-test-top',
            'price' => 199, 'final_price' => 199, 'status' => 'active',
        ]);
        // L had 5 pieces: 3 paid orders already deducted, so 2 left on the shelf.
        $sizeL = \App\Models\ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock' => 2]);

        $makeItem = function (string $number, bool $stockReview = false) use ($product, $sizeL): OrderItem {
            $order = Order::create([
                'order_number' => $number, 'customer_name' => 'Buyer ' . $number, 'customer_phone' => '98765' . random_int(10000, 99999),
                'house_building' => 'House', 'street' => 'Street', 'area' => 'Area', 'city' => 'Kochi',
                'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => '682001',
                'subtotal' => 199, 'grand_total' => 199, 'payment_method' => 'online',
                'payment_status' => 'paid', 'order_status' => $stockReview ? 'pending' : 'confirmed',
            ]);
            \App\Models\Payment::create([
                'order_id' => $order->id, 'payment_method' => 'online', 'amount' => 199, 'status' => 'paid',
                'response_payload' => ['stock_review' => $stockReview ? 'Stock validation failed' : null],
            ]);
            return OrderItem::create([
                'order_id' => $order->id, 'product_id' => $product->id, 'product_size_id' => $sizeL->id, 'product_name' => $product->name,
                'size' => 'L', 'unit_price' => 199, 'final_unit_price' => 199, 'quantity' => 1, 'subtotal' => 199, 'item_status' => 'active',
            ]);
        };
        $book = fn () => \App\Models\ProductBooking::create([
            'product_id' => $product->id, 'product_size_id' => $sizeL->id, 'quantity' => 1, 'booked_by' => 'Booked Customer',
            'booking_type' => 'whatsapp_customer', 'booking_date' => now()->toDateString(), 'status' => 'active',
        ]);
        $url = route('admin.reports.duplicate-paid-products');

        // 3 paid + 2 booked = 5 = stock: fine.
        $makeItem('OK-1'); $makeItem('OK-2'); $makeItem('OK-3');
        $book(); $secondBooking = $book();
        $this->actingAs($admin)->get($url)->assertOk()->assertDontSee('Oversold Test Top');

        // A 4th buyer pays but gets no stock: 6 promised vs 5 pieces.
        $makeItem('EXTRA-BUYER', true);
        $this->actingAs($admin)->get($url)->assertOk()
            ->assertSee('Oversold Test Top')->assertSee('1 extra')->assertSee('EXTRA-BUYER')
            ->assertSee('No stock – refund/review', false)->assertSee('Booked Customer');

        // Releasing one booking makes it balance again.
        $secondBooking->update(['status' => 'released']);
        $this->actingAs($admin)->get($url)->assertOk()->assertDontSee('Oversold Test Top');
    }
}
