<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingChargeAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected ProductSize $productSize;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@quara.test',
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Dresses',
            'slug' => 'dresses',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Elegant Dress',
            'slug' => 'elegant-dress',
            'price' => 200.00,
            'cost_price' => 100.00,
            'discount_type' => 'none',
            'discount_value' => 0.00,
            'final_price' => 200.00,
            'status' => 'active',
        ]);

        $this->productSize = ProductSize::create([
            'product_id' => $this->product->id,
            'size' => 'M',
            'stock' => 50,
        ]);
    }

    /** @test */
    public function order_edit_shipping_charge_increase_increases_total_and_recalculates()
    {
        $this->actingAs($this->admin);

        $order = Order::create([
            'order_number' => 'QW-20260905-00001',
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'house_building' => 'No 12',
            'street' => 'Main St',
            'area' => 'Downtown',
            'city' => 'Kochi',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'pin_code' => '682001',
            'subtotal' => 200.00,
            'discount' => 0.00,
            'shipping' => 10.00,
            'grand_total' => 210.00,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'product_name' => $this->product->name,
            'size' => $this->productSize->size,
            'unit_price' => 200.00,
            'discount_amount' => 0.00,
            'final_unit_price' => 200.00,
            'quantity' => 1,
            'subtotal' => 200.00,
            'item_status' => 'active',
        ]);

        // Increase shipping charge from 10 to 20
        $response = $this->put(route('admin.orders.update', $order->id), [
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'house_building' => 'No 12',
            'street' => 'Main St',
            'area' => 'Downtown',
            'city' => 'Kochi',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'pin_code' => '682001',
            'order_status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'shipping' => 20.00,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));

        $order->refresh();
        $this->assertEquals(20.00, (float) $order->shipping);
        $this->assertEquals(220.00, (float) $order->grand_total);
    }

    /** @test */
    public function manual_sale_edit_shipping_charge_decrease_decreases_total()
    {
        $this->actingAs($this->admin);

        $order = Order::create([
            'order_number' => 'QW-MAN-TEST1',
            'customer_name' => 'Jane Smith',
            'customer_phone' => '9876543211',
            'house_building' => 'Store',
            'street' => 'Counter',
            'area' => 'Naduvil',
            'city' => 'Naduvil',
            'district' => 'Kannur',
            'state' => 'Kerala',
            'pin_code' => '670582',
            'subtotal' => 200.00,
            'discount' => 0.00,
            'shipping' => 10.00,
            'grand_total' => 210.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'order_source' => 'manual',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'product_name' => $this->product->name,
            'size' => $this->productSize->size,
            'unit_price' => 200.00,
            'discount_amount' => 0.00,
            'final_unit_price' => 200.00,
            'quantity' => 1,
            'subtotal' => 200.00,
            'item_status' => 'active',
        ]);

        // Decrease delivery charge from 10 to 5
        $response = $this->put(route('admin.manual-sales.update', $order->id), [
            'customer_name' => 'Jane Smith',
            'customer_phone' => '9876543211',
            'items' => [
                [
                    'product_size_id' => $this->productSize->id,
                    'quantity' => 1,
                    'unit_price' => 200.00,
                ]
            ],
            'delivery_charge' => 5.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.manual-sales.index'));

        $order->refresh();
        $this->assertEquals(5.00, (float) $order->shipping);
        $this->assertEquals(205.00, (float) $order->grand_total);
    }

    /** @test */
    public function order_adjust_update_shipping_recalculates_totals_and_razorpay_charges()
    {
        $this->actingAs($this->admin);

        $order = Order::create([
            'order_number' => 'QW-20260905-00002',
            'customer_name' => 'Alice Walker',
            'customer_phone' => '9876543212',
            'house_building' => 'Flat 3B',
            'street' => 'Park Ave',
            'area' => 'Central',
            'city' => 'Calicut',
            'district' => 'Kozhikode',
            'state' => 'Kerala',
            'pin_code' => '673001',
            'subtotal' => 500.00,
            'discount' => 50.00,
            'shipping' => 40.00,
            'grand_total' => 490.00,
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'product_name' => $this->product->name,
            'size' => $this->productSize->size,
            'unit_price' => 500.00,
            'discount_amount' => 50.00,
            'final_unit_price' => 450.00,
            'quantity' => 1,
            'subtotal' => 500.00,
            'item_status' => 'active',
        ]);

        // Update shipping via Order Adjust route
        $response = $this->post(route('admin.order-operations.update-shipping', $order->id), [
            'shipping' => 60.00,
        ]);

        $response->assertRedirect(route('admin.order-operations.create', $order->id));

        $order->refresh();
        // Subtotal (500) + Shipping (60) - Discount (50) = 510
        $this->assertEquals(60.00, (float) $order->shipping);
        $this->assertEquals(510.00, (float) $order->grand_total);

        // Razorpay charge should be auto-recalculated on grand_total 510 (Fee: 2% = 10.20, GST: 18% = 1.84, Total Charge = 12.04)
        $this->assertGreaterThan(0, (float) $order->razorpay_total_charge);
    }
}
