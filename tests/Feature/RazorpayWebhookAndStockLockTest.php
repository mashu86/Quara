<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use App\Services\RazorpayOrderService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        config(['services.razorpay.key' => 'rzp_test_example', 'services.razorpay.secret' => 'test-secret', 'services.razorpay.webhook_secret' => 'webhook-secret']);
        Http::preventStrayRequests();
        Mail::fake();

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
                        'currency' => 'INR',
                        'status' => 'captured',
                        'notes' => [
                            'order_number' => 'ORD-WEBHOOK-1',
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson(route('api.webhooks.razorpay'), $payload, [
            'X-Razorpay-Signature' => hash_hmac('sha256', json_encode($payload), 'webhook-secret'),
        ]);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->order_status);
        $this->assertNull($order->reserved_until);
        $this->assertEquals(0, $this->size->fresh()->stock); // Stock permanently deducted from 1 to 0
    }

    private function pendingOrder(string $number, array $overrides = []): Order
    {
        $order = Order::create($this->orderData(array_merge([
            'order_number' => $number, 'reserved_until' => now()->addMinutes(5),
        ], $overrides)));
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $this->product->id,
            'product_size_id' => $this->size->id, 'product_name' => $this->product->name,
            'size' => 'M', 'unit_price' => 1000, 'discount_amount' => 0,
            'final_unit_price' => 1000, 'quantity' => 1, 'subtotal' => 1000,
        ]);
        Payment::create([
            'order_id' => $order->id, 'payment_method' => 'online',
            'razorpay_order_id' => 'order_'.$number, 'status' => 'pending', 'amount' => 1000,
        ]);
        return $order;
    }

    private function captured(Order $order): array
    {
        return ['id' => 'pay_'.$order->id, 'order_id' => $order->payment->razorpay_order_id,
            'status' => 'captured', 'currency' => 'INR', 'amount' => 100000];
    }

    private function webhook(Order $order, string $event = 'payment.captured')
    {
        $data = $this->captured($order);
        $payload = ['event' => $event, 'payload' => ['payment' => ['entity' => $data]]];
        return $this->postJson(route('api.webhooks.razorpay'), $payload, [
            'X-Razorpay-Signature' => hash_hmac('sha256', json_encode($payload), 'webhook-secret'),
        ]);
    }

    public function test_cancelled_orders_are_never_checked_or_reconfirmed(): void
    {
        $order = $this->pendingOrder('CANCELLED', ['order_status' => 'cancelled']);
        $this->actingAs($this->admin)->postJson(route('admin.orders.auto-sync-pending'))
            ->assertOk()->assertJson(['synced_count' => 0]);
        $this->post(route('admin.orders.recheck-razorpay', $order))->assertRedirect();
        $this->post(route('admin.payment-discrepancies.reconcile', $order))->assertRedirect();
        Http::assertNothingSent();
        $this->webhook($order)->assertJson(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $order->fresh()->order_status);
        $this->assertEquals(1, $this->size->fresh()->stock);
        $this->assertEquals(1, $this->size->fresh()->available_stock);
    }

    public function test_duplicate_webhook_and_sync_deduct_stock_only_once(): void
    {
        $order = $this->pendingOrder('DUPLICATE');
        $this->webhook($order)->assertJson(['status' => 'success']);
        $this->webhook($order)->assertJson(['status' => 'already_processed']);
        $this->assertEquals('already_processed', app(RazorpayOrderService::class)->confirm($order, $this->captured($order), 'Auto Sync'));
        $this->assertEquals(0, $this->size->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_late_payment_cannot_take_another_customers_reserved_stock(): void
    {
        $late = $this->pendingOrder('LATE', ['reserved_until' => now()->subMinute()]);
        $current = $this->pendingOrder('CURRENT');
        $this->webhook($late)->assertJson(['status' => 'stock_review']);
        $this->assertEquals('paid', $late->fresh()->payment_status);
        $this->assertEquals('pending', $late->fresh()->order_status);
        $this->assertEquals(1, $this->size->fresh()->stock);
        $this->webhook($current)->assertJson(['status' => 'success']);
        $this->assertEquals(0, $this->size->fresh()->stock);
        $this->webhook($late)->assertJson(['status' => 'stock_review']);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->get(route('checkout.success', ['order_number' => $late->order_number]))
            ->assertSee('ORDER UNDER REVIEW')->assertDontSee('is being prepared with care');
    }

    public function test_stock_is_never_silently_deducted_below_zero(): void
    {
        $first = $this->pendingOrder('FIRST', ['reserved_until' => now()->subMinute()]);
        $second = $this->pendingOrder('SECOND', ['reserved_until' => now()->subMinute()]);
        $this->webhook($first)->assertJson(['status' => 'success']);
        $this->webhook($second)->assertJson(['status' => 'stock_review']);
        $this->assertEquals(1, Order::where('order_status', 'confirmed')->count());
        $this->assertEquals(0, $this->size->fresh()->stock);
    }

    public function test_checkout_rechecks_reserved_stock_even_after_initial_validation(): void
    {
        $this->pendingOrder('RESERVED');
        $this->expectExceptionMessage('Stock validation failed');
        DB::transaction(fn () => app(StockService::class)->lockAndValidateCheckoutStock([
            ['product_id' => $this->product->id, 'size' => 'M', 'quantity' => 1],
        ]));
    }

    public function test_two_checkouts_cannot_open_payment_for_the_last_item(): void
    {
        $cart = [
            'product_id' => $this->product->id, 'name' => $this->product->name,
            'size' => 'M', 'price' => 1000, 'discount_amount' => 0,
            'final_price' => 1000, 'quantity' => 1, 'subtotal' => 1000,
        ];
        Http::fake(['api.razorpay.com/v1/orders' => Http::response(['id' => 'order_checkout'])]);
        $this->withSession(['cart' => ['item' => $cart]])->post(route('checkout.process'), $this->orderData())->assertOk();
        $this->withSession(['cart' => ['item' => $cart]])->post(route('checkout.process'), $this->orderData())->assertRedirect(route('cart.index'));
        Http::assertSentCount(1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals(0, $this->size->fresh()->available_stock);
    }

    public function test_authorized_or_wrong_amount_payments_are_not_confirmed(): void
    {
        $order = $this->pendingOrder('VALIDATION');
        $service = app(RazorpayOrderService::class);
        foreach ([['status' => 'authorized'], ['amount' => 100], ['order_id' => 'order_other'], ['currency' => 'USD']] as $change) {
            $this->assertEquals('payment_mismatch', $service->confirm($order, array_merge($this->captured($order), $change), 'Test'));
        }
        $this->assertEquals('pending', $order->fresh()->payment_status);
        $this->assertEquals(1, $this->size->fresh()->stock);
    }

    public function test_unsigned_webhook_cannot_confirm_an_order(): void
    {
        $order = $this->pendingOrder('UNSIGNED');
        $this->postJson(route('api.webhooks.razorpay'), ['event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => $this->captured($order)]]])->assertStatus(400);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_failed_attempt_preserves_hold_for_a_payment_retry(): void
    {
        $order = $this->pendingOrder('RETRY');
        $this->webhook($order, 'payment.failed')->assertOk();
        $this->assertEquals(0, $this->size->fresh()->available_stock);
        $this->webhook($order)->assertJson(['status' => 'success']);
        $this->assertEquals(0, $this->size->fresh()->stock);
    }

    public function test_cancelling_an_unpaid_order_releases_hold_without_adding_stock(): void
    {
        $order = $this->pendingOrder('CANCEL-UNPAID');
        $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'cancelled', 'payment_status' => 'pending',
        ])->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->order_status);
        $this->assertNull($order->fresh()->reserved_until);
        $this->assertEquals(1, $this->size->fresh()->stock);
        $this->assertEquals(1, $this->size->fresh()->available_stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_cancelling_a_paid_order_restores_stock_only_once(): void
    {
        $order = $this->pendingOrder('CANCEL-PAID');
        $this->webhook($order)->assertJson(['status' => 'success']);
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order), [
                'order_status' => 'cancelled', 'payment_status' => 'paid',
            ])->assertRedirect();
        }
        $this->assertEquals(1, $this->size->fresh()->stock);
        $this->assertFalse((bool) $this->product->fresh()->is_out_of_stock);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_sync_rechecks_cancellation_after_the_gateway_response(): void
    {
        $order = $this->pendingOrder('CANCEL-DURING-SYNC');
        Http::fake(function () use ($order) {
            Order::whereKey($order->id)->update(['order_status' => 'cancelled']);
            return Http::response(['items' => [$this->captured($order)]]);
        });
        $this->actingAs($this->admin)->postJson(route('admin.orders.auto-sync-pending'))->assertJson(['synced_count' => 0]);
        $this->assertEquals('cancelled', $order->fresh()->order_status);
        $this->assertEquals('pending', $order->fresh()->payment_status);
        $this->assertEquals(1, $this->size->fresh()->stock);
    }

    public function test_callback_then_webhook_deducts_stock_once(): void
    {
        $order = $this->pendingOrder('CALLBACK');
        $data = $this->captured($order);
        Http::fake(['api.razorpay.com/v1/payments/*' => Http::response($data)]);
        $signature = hash_hmac('sha256', $data['order_id'].'|'.$data['id'], 'test-secret');
        $this->post(route('checkout.verify_online_payment'), [
            'order_number' => $order->order_number, 'razorpay_payment_id' => $data['id'],
            'razorpay_order_id' => $data['order_id'], 'razorpay_signature' => $signature,
        ])->assertRedirect(route('checkout.success', ['order_number' => $order->order_number]));
        $this->webhook($order)->assertJson(['status' => 'already_processed']);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_partial_stock_deduction_rolls_back_when_a_later_item_is_unavailable(): void
    {
        $order = $this->pendingOrder('MULTI-ITEM');
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $this->product->id,
            'product_name' => $this->product->name, 'size' => 'Z', 'unit_price' => 0,
            'final_unit_price' => 0, 'quantity' => 1, 'subtotal' => 0,
        ]);
        $this->webhook($order)->assertJson(['status' => 'stock_review']);
        $this->assertEquals(1, $this->size->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('pending', $order->fresh()->order_status);
    }

    public function test_manual_sync_can_resolve_stock_review_after_real_stock_is_added(): void
    {
        $order = $this->pendingOrder('REVIEW-RESOLVED');
        $this->size->update(['stock' => 0]);
        $this->webhook($order)->assertJson(['status' => 'stock_review']);
        $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'confirmed', 'payment_status' => 'paid',
        ])->assertSessionHas('error');
        $this->size->update(['stock' => 1]);
        Http::fake(['api.razorpay.com/v1/payments/*' => Http::response($this->captured($order))]);
        $this->post(route('admin.orders.recheck-razorpay', $order))->assertSessionHas('success');
        $this->assertEquals('confirmed', $order->fresh()->order_status);
        $this->assertEquals(0, $this->size->fresh()->stock);
        $this->assertNull($order->payment()->first()->response_payload['stock_review']);
        $this->webhook($order)->assertJson(['status' => 'already_processed']);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_callback_cannot_reopen_cancelled_order_or_query_razorpay(): void
    {
        $order = $this->pendingOrder('CANCEL-CALLBACK', ['order_status' => 'cancelled']);
        $data = $this->captured($order);
        $this->post(route('checkout.verify_online_payment'), [
            'order_number' => $order->order_number, 'razorpay_payment_id' => $data['id'],
            'razorpay_order_id' => $data['order_id'],
            'razorpay_signature' => hash_hmac('sha256', $data['order_id'].'|'.$data['id'], 'test-secret'),
        ])->assertRedirect(route('checkout.success', ['order_number' => $order->order_number]));
        Http::assertNothingSent();
        $this->assertEquals('cancelled', $order->fresh()->order_status);
    }

    public function test_payment_initialization_failure_does_not_open_unbound_checkout(): void
    {
        $order = $this->pendingOrder('INIT-FAILURE');
        Http::fake(['api.razorpay.com/v1/orders' => Http::response(['error' => 'unavailable'], 503)]);
        try {
            app(\App\Services\PaymentService::class)->initiatePayment($order, 'online');
            $this->fail('Expected payment initialization to fail.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Unable to start payment', $e->getMessage());
        }
        $this->assertNull($order->fresh()->reserved_until);
        $this->assertDatabaseCount('payments', 1);
    }
}
