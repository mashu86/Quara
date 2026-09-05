<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpayWebhookAndStockLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected ProductSize $size;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create([
            'name' => 'Boutique Collection',
            'slug' => 'boutique-collection',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Unique Boutique Dress',
            'slug' => 'unique-boutique-dress',
            'price' => 1000,
            'final_price' => 1000,
            'status' => 'active',
        ]);

        // Single stock item (quantity = 1)
        $this->size = ProductSize::create([
            'product_id' => $this->product->id,
            'size' => 'M',
            'stock' => 1,
        ]);
    }

    private function orderData(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Customer',
            'customer_phone' => '9876543210',
            'customer_email' => 'test@example.com',
            'house_building' => 'House 1',
            'street' => 'Street 2',
            'area' => 'Area 3',
            'city' => 'Kochi',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'pin_code' => '682001',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'payment_method' => 'online',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'is_legacy_pending' => false,
        ], $overrides);
    }

    public function test_legacy_pending_orders_are_separated_from_main_orders_list(): void
    {
        $legacyOrder = Order::create($this->orderData([
            'order_number' => 'ORD-OLD-001',
            'customer_name' => 'Legacy Customer',
            'is_legacy_pending' => true,
        ]));

        $activeOrder = Order::create($this->orderData([
            'order_number' => 'ORD-NEW-002',
            'customer_name' => 'Active Customer',
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'is_legacy_pending' => false,
        ]));

        // Main orders index should NOT show legacy order by default
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('ORD-NEW-002');
        $response->assertDontSee('ORD-OLD-001');

        // Filtering by old_pending status SHOULD show legacy order
        $legacyResponse = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'old_pending']));
        $legacyResponse->assertStatus(200);
        $legacyResponse->assertSee('ORD-OLD-001');
        $legacyResponse->assertDontSee('ORD-NEW-002');
    }

    public function test_5_minute_stock_lock_prevents_double_booking(): void
    {
        // Initial available stock is 1
        $this->assertEquals(1, $this->size->fresh()->available_stock);

        // User 1 starts checkout for size M
        $pendingOrder = Order::create($this->orderData([
            'order_number' => 'ORD-USER1',
            'customer_name' => 'User One',
            'reserved_until' => now()->addMinutes(5),
        ]));

        OrderItem::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $this->product->id,
            'product_size_id' => $this->size->id,
            'product_name' => $this->product->name,
            'size' => 'M',
            'unit_price' => 1000,
            'discount_amount' => 0,
            'final_unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        // Stock available should now be 0 during the 5 minute reservation
        $this->assertEquals(0, $this->size->fresh()->available_stock);

        // If reservation expires (simulated in future time), stock becomes 1 again
        $pendingOrder->update(['reserved_until' => now()->subMinute()]);
        $this->assertEquals(1, $this->size->fresh()->available_stock);
    }

    public function test_razorpay_webhook_captured_confirms_order_and_deducts_stock(): void
    {
        $order = Order::create($this->orderData([
            'order_number' => 'ORD-WEBHOOK-1',
            'customer_name' => 'Webhook Buyer',
            'reserved_until' => now()->addMinutes(5),
        ]));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_size_id' => $this->size->id,
            'product_name' => $this->product->name,
            'size' => 'M',
            'unit_price' => 1000,
            'discount_amount' => 0,
            'final_unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'online',
            'razorpay_order_id' => 'order_test_12345',
            'status' => 'pending',
            'amount' => 1000,
        ]);

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test_9999',
                        'order_id' => 'order_test_12345',
                        'amount' => 100000,
                        'status' => 'captured',
                        'notes' => [
                            'order_number' => 'ORD-WEBHOOK-1',
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson(route('api.webhooks.razorpay'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->order_status);
        $this->assertNull($order->reserved_until);
        $this->assertEquals(0, $this->size->fresh()->stock); // Stock permanently deducted from 1 to 0
    }
}
