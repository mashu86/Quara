<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEditCustomerDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        return Order::create([
            'order_number' => 'ORD-EDIT-1', 'customer_name' => 'Old Name', 'customer_phone' => '9876543210',
            'house_building' => 'House 1', 'street' => 'Street 2', 'area' => 'Area 3', 'city' => 'Kochi',
            'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => '682001',
            'subtotal' => 1000, 'grand_total' => 1000, 'payment_method' => 'online',
            'payment_status' => 'paid', 'order_status' => 'confirmed',
        ]);
    }

    public function test_name_and_address_are_locked_until_edit_and_can_be_filled_from_an_image(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $order = $this->order();

        $this->get(route('admin.orders.edit', $order))->assertOk()
            ->assertSee('Edit Name &amp; Address', false)->assertSee('Fill from Address Image')
            ->assertSee('name="customer_name" class="form-control rounded-3 customer-detail-field" readonly', false)
            ->assertSee('manual-sales\/parse-address', false)
            ->assertSee('id="geminiApiKeyPickerModal"', false)->assertSee('data-bs-target="#geminiApiKeyPickerModal"', false);
    }

    public function test_saving_changes_the_name_and_address(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $order = $this->order();

        $this->put(route('admin.orders.update', $order), [
            'customer_name' => 'New Name', 'customer_phone' => '9123456789', 'house_building' => 'Rose Villa',
            'street' => 'MG Road', 'area' => 'Near Bus Stand', 'city' => 'Kannur', 'district' => 'Kannur',
            'state' => 'Kerala', 'pin_code' => '670001',
            'order_status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => 'online',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame(['New Name', '9123456789', 'Rose Villa', 'Kannur', '670001'],
            [$order->customer_name, $order->customer_phone, $order->house_building, $order->city, $order->pin_code]);
    }
}
