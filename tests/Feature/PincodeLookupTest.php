<?php

namespace Tests\Feature;

use App\Services\PincodeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PincodeLookupTest extends TestCase
{
    public function test_bundled_pins_resolve_without_any_external_connection(): void
    {
        Http::preventStrayRequests();
        Http::fake();
        foreach (['670001', '670582'] as $pin) {
            $this->assertSame(['district' => 'Kannur', 'state' => 'Kerala'], app(PincodeService::class)->lookup($pin));
        }
        $this->getJson(route('address.pincode', ['pin_code' => '670001']))
            ->assertOk()->assertJson(['district' => 'Kannur', 'state' => 'Kerala']);
        Http::assertNothingSent();
    }

    public function test_verified_fallback_is_cached_and_invalid_pin_is_rejected(): void
    {
        Cache::flush();
        Http::fake(['api.postalpincode.in/*' => Http::response([
            ['Status' => 'Success', 'PostOffice' => [['District' => 'Kannur', 'State' => 'Kerala']]],
        ])]);
        $pins = app(PincodeService::class);
        $this->assertSame('Kannur', $pins->lookup('999998')['district']);
        $this->assertSame('Kannur', $pins->lookup('999998')['district']);
        Http::assertSentCount(1);
        $this->getJson(route('address.pincode', ['pin_code' => 'abc']))->assertUnprocessable();
    }
}
