<?php

namespace Tests\Feature;

use App\Models\{Category, DistrictOffer, Order, Product, ProductSize, User};
use App\Services\{DistrictOfferService, PaymentService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Http, Mail};
use Tests\TestCase;

class DistrictOfferTest extends TestCase
{
    use RefreshDatabase;

    private function offer(array $changes = []): DistrictOffer
    {
        return DistrictOffer::create(array_replace([
            'district' => 'Kannur', 'state' => 'Kerala', 'start_date' => '2026-09-01',
            'end_date' => '2026-09-30', 'method' => 'percentage', 'value' => 10, 'is_active' => true,
        ], $changes));
    }

    private function address(): array
    {
        return ['customer_name' => 'Test Customer', 'customer_phone' => '9876543210', 'customer_email' => 'test@example.com',
            'house_building' => 'House 1', 'street' => 'Main Street', 'area' => 'Market', 'city' => 'Kannur',
            'district' => 'Kannur', 'state' => 'Kerala', 'pin_code' => '670001'];
    }

    private function productSize(): ProductSize
    {
        $category = Category::create(['name' => 'Clothes', 'slug' => 'clothes', 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Dress', 'slug' => 'dress',
            'price' => 1000, 'cost_price' => 500, 'discount_type' => 'fixed', 'discount_value' => 100, 'status' => 'active']);

        return ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock' => 20]);
    }

    private function fakePin(string $district = 'Kannur'): void
    {
        Http::preventStrayRequests();
        Http::fake(['api.postalpincode.in/*' => Http::response([
            ['Status' => 'Success', 'PostOffice' => [['District' => $district, 'State' => 'Kerala']]],
        ])]);
    }

    public function test_dates_are_inclusive_and_latest_inactive_or_expired_version_never_revives_old_offer(): void
    {
        $offer = $this->offer();
        $service = app(DistrictOfferService::class);
        $this->assertSame($offer->id, $service->eligible(' Cannanore ', 'kerala', '2026-09-01')->id);
        $this->assertSame($offer->id, $service->eligible('Kannur', 'Kerala', '2026-09-30')->id);
        $this->assertNull($service->eligible('Kannur', 'Kerala', '2026-08-31'));
        $this->assertNull($service->eligible('Kannur', 'Kerala', '2026-10-01'));
        $this->assertNull($service->eligible('Kannur', 'Karnataka', '2026-09-20'));
        $this->offer(['is_active' => false]);
        $this->assertNull($service->eligible('Kannur', 'Kerala', '2026-09-20'));
        $this->offer(['start_date' => '2026-08-01', 'end_date' => '2026-08-31']);
        $this->assertNull($service->eligible('Kannur', 'Kerala', '2026-09-20'));
    }

    public function test_admin_updates_insert_versions_and_validate_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $original = $this->offer();
        $data = ['start_date' => '2026-09-01', 'end_date' => '2026-09-30', 'method' => 'percentage', 'value' => 20, 'is_active' => '0'];
        $this->actingAs($admin)->put(route('admin.district-offers.update', 'Kannur'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('district_offers', 2);
        $this->assertTrue($original->fresh()->is_active);
        $this->assertEquals(10, $original->fresh()->value);
        $this->actingAs($admin)->put(route('admin.district-offers.update', 'Kannur'), array_replace($data, ['value' => 101]))->assertSessionHasErrors('value');
        $this->actingAs($admin)->put(route('admin.district-offers.update', 'Kannur'), array_replace($data, ['end_date' => '2026-08-31']))->assertSessionHasErrors('end_date');
        $this->actingAs($admin)->get(route('admin.district-offers.index'))->assertOk()->assertSee('District Offers')->assertSee('Wayanad');
    }

    public function test_offer_page_automatically_loads_latest_saved_district_and_respects_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kannur = $this->offer();
        $latest = $this->offer(['district' => 'Kollam', 'method' => 'fixed', 'value' => 75, 'is_active' => false]);
        $this->actingAs($admin)->get(route('admin.district-offers.index'))
            ->assertOk()->assertViewHas('selectedDistrict', 'Kollam')
            ->assertViewHas('offer', fn ($offer) => $offer->id === $latest->id)
            ->assertSee('value="75.00"', false)->assertSee('value="2026-09-30"', false);
        $this->withSession(['_old_input' => ['district' => 'Kollam', 'value' => 999]])
            ->get(route('admin.district-offers.index', ['district' => 'Kannur']))
            ->assertOk()->assertViewHas('selectedDistrict', 'Kannur')
            ->assertViewHas('offer', fn ($offer) => $offer->id === $kannur->id)
            ->assertSee('value="10.00"', false)->assertDontSee('value="999"', false);
    }

    public function test_checkout_uses_verified_pin_and_saves_discount_after_product_discount(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20));
        $this->fakePin();
        $offer = $this->offer();
        $size = $this->productSize();
        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('initiatePayment')->once()->withArgs(fn ($order, $method) => (float) $order->grand_total === 810.0)
                ->andReturn(['razorpay_order_id' => 'order_mock_local_test', 'razorpay_key' => 'test', 'amount' => 81000]);
        });
        $cart = ['dress_M' => ['product_id' => $size->product_id, 'size' => 'M', 'quantity' => 1]];
        $this->withSession(['cart' => $cart])->getJson(route('checkout.district-offer', ['pin_code' => '670001']))
            ->assertOk()->assertJsonPath('district', 'Kannur')->assertJsonPath('discount', 90)->assertJsonPath('grand_total', 810);
        $this->post(route('checkout.process'), array_replace($this->address(), ['district' => 'Ernakulam', 'state' => 'Other', 'payment_method' => 'online']))
            ->assertOk()->assertSee('District Wise Special Offer');
        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals(190, $order->discount);
        $this->assertEquals(90, $order->district_offer_discount);
        $this->assertSame('Ernakulam', $order->district);
        $this->assertSame('Other', $order->state);
        $this->assertSame($offer->id, $order->district_offer_id);
        $this->offer(['is_active' => false, 'value' => 50]);
        $order->refresh()->recalculateTotals();
        $this->assertEquals(810, $order->fresh()->grand_total);
        $this->assertEquals(10, $order->fresh()->district_offer_snapshot['value']);
        $this->get(route('checkout.success', $order->order_number))->assertOk()->assertSee('District Wise Special Offer')->assertSee('90.00');
    }

    public function test_pin_failure_is_explicit_and_does_not_create_an_order(): void
    {
        Http::fake(['api.postalpincode.in/*' => Http::response([['Status' => 'Error', 'PostOffice' => null]])]);
        $this->getJson(route('checkout.district-offer', ['pin_code' => '000000']))->assertUnprocessable();
        $this->getJson(route('checkout.district-offer', ['pin_code' => '999999']))->assertUnprocessable()->assertJsonValidationErrors('pin_code');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_manual_checkout_address_can_be_saved_when_pin_lookup_is_unavailable(): void
    {
        $size = $this->productSize();
        $this->mock(\App\Services\PincodeService::class)->shouldReceive('lookup')->once()
            ->andThrow(\Illuminate\Validation\ValidationException::withMessages(['pin_code' => 'Unavailable']));
        $this->mock(PaymentService::class)->shouldReceive('initiatePayment')->once()
            ->andReturn(['razorpay_order_id' => 'order_test', 'razorpay_key' => 'test', 'amount' => 90000]);
        $this->withSession(['cart' => ['dress_M' => ['product_id' => $size->product_id, 'size' => 'M', 'quantity' => 1]]])
            ->post(route('checkout.process'), $this->address() + ['payment_method' => 'online'])->assertOk();
        $order = Order::firstOrFail();
        $this->assertSame('Kannur', $order->district);
        $this->assertSame('Kerala', $order->state);
        $this->assertEquals(0, $order->district_offer_discount);
    }

    public function test_offline_yes_uses_sale_date_and_edit_and_invoice_keep_snapshot_after_switch_off(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 5));
        $admin = User::factory()->create(['role' => 'admin']);
        $size = $this->productSize();
        $this->offer(['method' => 'fixed', 'value' => 125]);
        $payload = $this->address() + ['items' => [['product_size_id' => $size->id, 'quantity' => 1, 'unit_price' => 1000]],
            'sale_date' => '2026-09-30', 'payment_method' => 'cash', 'payment_status' => 'pending',
            'delivery_charge' => 50, 'discount_option' => 'calculate_discount', 'discount_type' => 'fixed', 'discount_value' => 100,
            'provide_district_offer' => '1'];
        $this->actingAs($admin)->post(route('admin.manual-sales.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals(825, $order->grand_total);
        $this->assertEquals(125, $order->district_offer_discount);
        $this->offer(['is_active' => false]);
        $this->put(route('admin.manual-sales.update', $order), $payload)->assertSessionHasNoErrors();
        $this->assertEquals(825, $order->fresh()->grand_total);
        $this->assertEquals(125, $order->fresh()->district_offer_discount);
        $this->get(route('admin.orders.invoice', $order))->assertOk()->assertSee('District Wise Special Offer')->assertSee('125.00');
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('District Wise Special Offer');
        $this->get(route('admin.manual-sales.edit', $order))->assertOk()->assertSee('District Wise Special Offer');
    }

    public function test_offline_decline_records_eligibility_without_discount_and_choice_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $size = $this->productSize();
        $this->offer();
        $payload = $this->address() + ['items' => [['product_size_id' => $size->id, 'quantity' => 1, 'unit_price' => 1000]],
            'sale_date' => '2026-09-01', 'payment_method' => 'cash', 'payment_status' => 'pending'];
        $this->actingAs($admin)->post(route('admin.manual-sales.store'), $payload)->assertSessionHasErrors('provide_district_offer');
        $this->assertDatabaseCount('orders', 0);
        $this->post(route('admin.manual-sales.store'), $payload + ['provide_district_offer' => '0'])->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertEquals(1000, $order->grand_total);
        $this->assertEquals(0, $order->district_offer_discount);
        $this->assertFalse($order->district_offer_accepted);
        $this->assertEquals(100, $order->district_offer_snapshot['eligible_amount']);
    }

    public function test_discount_is_capped_and_snapshot_cannot_be_changed(): void
    {
        $snapshot = app(DistrictOfferService::class)->snapshot($this->offer(['method' => 'fixed', 'value' => 2000]), 500, true);
        $this->assertEquals(500, $snapshot['district_offer_discount']);
        $order = Order::create($this->address() + $snapshot + ['order_number' => 'SNAPSHOT-TEST', 'subtotal' => 500, 'discount' => 500,
            'shipping' => 0, 'grand_total' => 0, 'payment_method' => 'cash', 'payment_status' => 'paid', 'order_status' => 'delivered']);
        $this->expectException(\LogicException::class);
        $order->update(['district_offer_discount' => 0]);
    }

    public function test_admin_offer_routes_require_login_and_preview_uses_selected_sale_date(): void
    {
        $this->get(route('admin.district-offers.index'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.district-offers.update', 'Kannur'), [])->assertRedirect(route('admin.login'));
        $admin = User::factory()->create(['role' => 'admin']);
        $this->offer();
        $this->actingAs($admin)->getJson(route('admin.district-offers.preview', ['district' => 'Kannur', 'state' => 'Kerala', 'sale_date' => '2026-09-30']))
            ->assertOk()->assertJsonPath('offer.district', 'Kannur');
        $this->getJson(route('admin.district-offers.preview', ['district' => 'Kannur', 'state' => 'Kerala', 'sale_date' => '2026-10-01']))
            ->assertOk()->assertJsonPath('offer', null);
    }

    public function test_full_discount_checkout_confirms_without_charging_gateway(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20));
        Mail::fake();
        $this->fakePin();
        $this->offer(['value' => 100]);
        $size = $this->productSize();
        $this->mock(PaymentService::class)->shouldNotReceive('initiatePayment');
        $this->mock(\App\Services\WhatsAppService::class)->shouldReceive('sendOrderConfirmation')->once();
        $addressWithoutEmail = $this->address();
        unset($addressWithoutEmail['customer_email']);
        $this->withSession(['cart' => ['dress_M' => ['product_id' => $size->product_id, 'size' => 'M', 'quantity' => 1]]])
            ->post(route('checkout.process'), $addressWithoutEmail + ['payment_method' => 'online'])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertNull($order->customer_email);
        $this->assertEquals(0, $order->grand_total);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertEquals(19, $size->fresh()->stock);
    }

    public function test_300_rupee_order_with_21_shipping_and_ten_percent_offer_costs_291_online_and_offline(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20));
        $this->fakePin();
        $this->offer();
        $size = $this->productSize();
        $size->product->update(['price' => 300, 'discount_type' => 'none', 'discount_value' => 0]);
        \App\Models\ShippingPolicy::create([
            'name' => 'Delivery', 'criteria_type' => 'cart_count', 'from_value' => 1,
            'from_operator' => '>=', 'delivery_type' => 'custom', 'charge_amount' => 21,
            'status' => 'active', 'priority' => 1,
        ]);
        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('initiatePayment')->once()
                ->withArgs(fn ($order, $method) => (float) $order->grand_total === 291.0)
                ->andReturn(['razorpay_order_id' => 'order_mock_local_test', 'razorpay_key' => 'test', 'amount' => 29100]);
        });
        $this->withSession(['cart' => ['dress_M' => ['product_id' => $size->product_id, 'size' => 'M', 'quantity' => 1]]])
            ->getJson(route('checkout.district-offer', ['pin_code' => '670001']))
            ->assertOk()->assertJsonPath('discount', 30)->assertJsonPath('grand_total', 291);
        $this->post(route('checkout.process'), $this->address() + ['payment_method' => 'online'])->assertOk();
        $online = Order::firstOrFail();
        $this->assertEquals(300, $online->district_offer_snapshot['base_amount']);
        $this->assertEquals(30, $online->district_offer_discount);
        $this->assertEquals(21, $online->shipping);
        $this->assertEquals(291, $online->grand_total);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.manual-sales.store'), $this->address() + [
            'items' => [['product_size_id' => $size->id, 'quantity' => 1, 'unit_price' => 300]],
            'sale_date' => '2026-09-20', 'delivery_charge' => 21, 'provide_district_offer' => '1',
            'payment_method' => 'cash', 'payment_status' => 'pending',
        ])->assertSessionHasNoErrors();
        $offline = Order::where('order_source', 'manual')->firstOrFail();
        $this->assertEquals(30, $offline->district_offer_discount);
        $this->assertEquals(21, $offline->shipping);
        $this->assertEquals(291, $offline->grand_total);
    }
}
