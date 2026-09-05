<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReturnRefundFinancialTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@quara.test',
            'role' => 'admin',
        ]);
    }

    public function test_product_return_creates_immutable_order_refund_with_specific_dates()
    {
        $category = Category::create([
            'name' => 'Dresses',
            'slug' => 'dresses',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Silk Saree',
            'slug' => 'silk-saree',
            'price' => 1000.00,
            'cost_price' => 500.00,
            'discount_type' => 'none',
            'discount_value' => 0.00,
            'final_price' => 1000.00,
            'status' => 'active',
        ]);

        $productSize = ProductSize::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-101',
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'house_building' => 'House 1',
            'street' => 'Street 1',
            'area' => 'Area 1',
            'city' => 'Kochi',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'pin_code' => '682001',
            'subtotal' => 1000.00,
            'shipping' => 50.00,
            'grand_total' => 1050.00,
            'effective_date' => '2026-09-01 10:00:00',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'order_status' => 'delivered',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'product_name' => $product->name,
            'size' => 'M',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'final_unit_price' => 1000.00,
            'subtotal' => 1000.00,
            'item_status' => 'active',
        ]);

        $returnDate = '2026-09-03';
        $refundDate = '2026-09-05';

        $response = $this->actingAs($this->admin)->post(route('admin.order-operations.store', $order->id), [
            'order_item_id' => $orderItem->id,
            'operation_type' => 'product_returned',
            'return_date' => $returnDate,
            'inventory_condition' => 'return_to_stock',
            'refund_option' => 'refund',
            'refund_amount' => 1000,
            'refund_date' => $refundDate,
            'notes' => 'Size fit issue',
        ]);

        $response->assertRedirect();

        $operation = OrderOperation::where('order_id', $order->id)->first();
        $this->assertNotNull($operation);
        $this->assertEquals('2026-09-03', $operation->return_date->format('Y-m-d'));
        $this->assertEquals(1000.00, (float) $operation->total_refund_amount);

        // Verify OrderRefund immutable record
        $this->assertEquals(1, OrderRefund::where('order_operation_id', $operation->id)->count());

        // Test adding an additional refund later on another date
        $additionalRefundDate = '2026-09-07';
        $addRefundResponse = $this->actingAs($this->admin)->post(route('admin.order-operations.add-refund', $operation->id), [
            'refund_amount' => 50,
            'refund_date' => $additionalRefundDate,
            'payment_method' => 'bank_transfer',
            'notes' => 'Courier fee refund',
        ]);

        $addRefundResponse->assertSessionHasNoErrors();
        $addRefundResponse->assertRedirect();

        $this->assertEquals(2, OrderRefund::where('order_operation_id', $operation->id)->count());

        // Verify total refunds on 2026-09-05 vs 2026-09-07
        $refundSept5 = OrderRefund::whereDate('refund_date', '2026-09-05')->sum('refund_amount');
        $refundSept7 = OrderRefund::whereDate('refund_date', '2026-09-07')->sum('refund_amount');

        $this->assertEquals(1000.00, (float) $refundSept5);
        $this->assertEquals(50.00, (float) $refundSept7);
    }
}
