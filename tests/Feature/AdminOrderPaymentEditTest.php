<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderPaymentEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_update_order_payment_details_from_pending_to_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Order::create([
            'order_number' => 'QW-20260831-00001',
            'customer_name' => 'Rahul Nair',
            'customer_phone' => '9876543210',
            'customer_email' => 'rahul@example.com',
            'house_building' => 'House #12',
            'street' => 'MG Road',
            'area' => 'Kochi',
            'city' => 'Kochi',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'pin_code' => '682001',
            'subtotal' => 1000.00,
            'discount' => 0.00,
            'shipping' => 50.00,
            'grand_total' => 1050.00,
            'payment_method' => 'online',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'online',
            'status' => 'pending',
            'amount' => 1050.00,
            'razorpay_order_id' => 'order_Rzp123456',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.update-payment-details', $order->id), [
                'payment_status' => 'paid',
                'payment_method' => 'online',
                'razorpay_payment_id' => 'pay_RzpPay999',
                'razorpay_order_id' => 'order_Rzp123456',
                'auto_confirm_order' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->order_status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('pay_RzpPay999', $payment->razorpay_payment_id);
    }

    public function test_admin_can_filter_orders_by_status_and_payment_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pendingOrder = Order::create([
            'order_number' => 'QW-20260831-00002',
            'customer_name' => 'Anu Mohan',
            'customer_phone' => '9876543211',
            'house_building' => 'Villa 5',
            'street' => 'Civil Station Road',
            'area' => 'Kozhikode',
            'city' => 'Kozhikode',
            'district' => 'Kozhikode',
            'state' => 'Kerala',
            'pin_code' => '673020',
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        $deliveredOrder = Order::create([
            'order_number' => 'QW-20260831-00003',
            'customer_name' => 'Vikas Menon',
            'customer_phone' => '9876543212',
            'house_building' => 'Flat 3B',
            'street' => 'Pattom',
            'area' => 'Trivandrum',
            'city' => 'Trivandrum',
            'district' => 'Thiruvananthapuram',
            'state' => 'Kerala',
            'pin_code' => '695004',
            'subtotal' => 1200.00,
            'grand_total' => 1200.00,
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
        ]);

        // Test filtering by order_status = pending
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'pending']));
        $response->assertOk();
        $response->assertSee($pendingOrder->order_number);
        $response->assertDontSee($deliveredOrder->order_number);

        // Test filtering by order_status = delivered
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'delivered']));
        $response->assertOk();
        $response->assertSee($deliveredOrder->order_number);
        $response->assertDontSee($pendingOrder->order_number);

        // Test filtering by payment_status = paid
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['payment_status' => 'paid']));
        $response->assertOk();
        $response->assertSee($deliveredOrder->order_number);
        $response->assertDontSee($pendingOrder->order_number);
    }
}
