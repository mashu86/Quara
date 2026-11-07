<?php

namespace Tests\Feature;

use App\Models\CourierAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierAddressTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Anjali Nair', 'customer_phone' => '9876543210',
            'house_building' => 'Rose Villa', 'street' => 'MG Road', 'city' => 'Kochi',
            'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => '682001',
        ], $overrides);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_address_can_be_added_and_listed(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.courier-addresses.store'), $this->payload())
            ->assertRedirect(route('admin.courier-addresses.index'));

        $this->actingAs($admin)->get(route('admin.courier-addresses.index'))
            ->assertOk()->assertSee('Anjali Nair')->assertSee('Rose Villa');
    }

    public function test_invalid_pin_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.courier-addresses.store'), $this->payload(['pin_code' => '123']))
            ->assertSessionHasErrors('pin_code');
        $this->assertSame(0, CourierAddress::count());
    }

    public function test_only_selected_addresses_print_with_sender(): void
    {
        $selected = CourierAddress::create($this->payload());
        CourierAddress::create($this->payload(['customer_name' => 'Other Person']));

        $this->actingAs($this->admin())->post(route('admin.courier-addresses.print'), ['address_ids' => [$selected->id], 'layout' => 'vertical'])
            ->assertOk()->assertSee('ANJALI NAIR')->assertDontSee('OTHER PERSON')
            ->assertSee('Akarsha Bakker')->assertSee('PH: 9876543210')
            ->assertSee(route('admin.courier-addresses.download-pdf'))
            ->assertSee('name="address_ids[]" value="'.$selected->id.'"', false);
    }

    public function test_selected_addresses_download_as_pdf(): void
    {
        $address = CourierAddress::create($this->payload());

        $response = $this->actingAs($this->admin())->post(route('admin.courier-addresses.download-pdf'), ['address_ids' => [$address->id], 'layout' => 'split']);

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('ANJALI NAIR', $response->getContent());
    }

    public function test_address_can_be_edited_and_deleted(): void
    {
        $admin = $this->admin();
        $address = CourierAddress::create($this->payload());

        $this->actingAs($admin)->get(route('admin.courier-addresses.edit', $address))->assertOk()->assertSee('Rose Villa');
        $this->actingAs($admin)->put(route('admin.courier-addresses.update', $address), $this->payload(['customer_name' => 'New Name']))
            ->assertRedirect(route('admin.courier-addresses.index'));
        $this->assertSame('New Name', $address->fresh()->customer_name);

        $this->actingAs($admin)->delete(route('admin.courier-addresses.destroy', $address));
        $this->assertSame(0, CourierAddress::count());
    }

    public function test_add_form_offers_gemini_key_picker(): void
    {
        $this->actingAs($this->admin())->get(route('admin.courier-addresses.create'))
            ->assertOk()->assertSee('geminiApiKeyPickerModal');
    }

    public function test_guest_cannot_view_addresses(): void
    {
        $this->get(route('admin.courier-addresses.index'))->assertRedirect();
    }
}
