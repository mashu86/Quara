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

        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products'));
        $response->assertOk()->assertDontSee('Report Test Shirt')->assertDontSee('RETURNED-ORDER')->assertDontSee('SELF-TEST-ORDER');

        $makeItem('PAID-ORDER-2-SAME-BUYER', '+91 98765 00002');
        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products'));
        $response->assertOk()->assertSee('Report Test Shirt')->assertSee('PAID-ORDER-1')->assertSee('PAID-ORDER-2-SAME-BUYER')
            ->assertDontSee('RETURNED-ORDER')->assertDontSee('SELF-TEST-ORDER');
    }
}
