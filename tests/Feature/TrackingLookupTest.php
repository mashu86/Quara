<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLookupTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, string $phone, string $paymentStatus = 'paid'): Order
    {
        return Order::create([
            'order_number' => $number, 'customer_name' => 'Anu K', 'customer_phone' => $phone,
            'house_building' => 'House 1', 'street' => 'MG Road', 'area' => 'Kakkanad', 'city' => 'Kochi',
            'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => '682030',
            'subtotal' => 1000, 'discount' => 0, 'shipping' => 0, 'grand_total' => 1000,
            'payment_method' => 'online', 'payment_status' => $paymentStatus, 'order_status' => 'shipped',
        ]);
    }

    public function test_phone_lookup_returns_tracking_numbers_without_personal_details(): void
    {
        $shipped = $this->order('QW-1', '+91 98765 43210');
        OrderShipment::create(['order_id' => $shipped->id, 'article_number' => 'CL516683385IN', 'tracking_number' => 'CL516683385IN', 'receiver_name' => 'Anu K', 'receiver_address' => 'Kochi']);
        $this->order('QW-2', '9876543210');
        $this->order('QW-3', '9876543210', 'pending');
        $this->order('QW-4', '9000000000');

        $response = $this->postJson(route('tracking.lookup'), ['phone' => '98765-43210'])->assertOk();

        $orders = collect($response->json('orders'))->keyBy('order_number');
        $this->assertSame(['QW-1', 'QW-2'], $orders->keys()->sort()->values()->all());
        $this->assertSame('CL516683385IN', $orders['QW-1']['tracking_number']);
        $this->assertNull($orders['QW-2']['tracking_number']);
        $response->assertDontSee('Anu K')->assertDontSee('Kakkanad');
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->postJson(route('tracking.lookup'), ['phone' => '12345'])->assertStatus(422);
    }
}
