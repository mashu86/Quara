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

    public function test_active_return_operation_excludes_the_returned_order_from_duplicate_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Report Category', 'slug' => 'report-category', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Report Test Shirt', 'slug' => 'report-test-shirt',
            'price' => 169, 'final_price' => 169, 'status' => 'active',
        ]);

        $makeItem = function (string $number) use ($product): OrderItem {
            $order = Order::create([
                'order_number' => $number, 'customer_name' => 'Report Customer', 'customer_phone' => '9876543210',
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

        $returnedItem = $makeItem('RETURNED-ORDER');
        OrderOperation::create([
            'order_id' => $returnedItem->order_id, 'order_item_id' => $returnedItem->id,
            'product_id' => $product->id, 'operation_type' => 'product_returned', 'status' => 'active',
        ]);
        $makeItem('PAID-ORDER-1');

        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products'));
        $response->assertOk()->assertDontSee('Report Test Shirt')->assertDontSee('RETURNED-ORDER');

        $makeItem('PAID-ORDER-2');
        $response = $this->actingAs($admin)->get(route('admin.reports.duplicate-paid-products'));
        $response->assertOk()->assertSee('Report Test Shirt')->assertSee('PAID-ORDER-1')->assertSee('PAID-ORDER-2')
            ->assertDontSee('RETURNED-ORDER');
    }
}
