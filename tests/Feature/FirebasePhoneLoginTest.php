<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebasePhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    private string $privateKey;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.firebase.project_id' => 'quara-wardrobe']);
        Cache::flush();

        $this->privateKey = file_get_contents(dirname(__DIR__).'/Fixtures/firebase-test-private.pem');
        $this->publicKey = file_get_contents(dirname(__DIR__).'/Fixtures/firebase-test-public.pem');

        Http::fake([
            'www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com' => Http::response(
                ['test-key-id' => $this->publicKey],
                200,
                ['Cache-Control' => 'public, max-age=300']
            ),
        ]);
    }

    public function test_otp_code_without_a_firebase_id_token_is_rejected(): void
    {
        $this->postJson(route('customer.phone-login'), ['otp' => '000000', 'phone' => '+919876543210'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');

        $this->assertGuest();
    }

    public function test_invalid_firebase_id_token_is_rejected(): void
    {
        $this->postJson(route('customer.phone-login'), ['id_token' => 'not-a-valid-jwt'])
            ->assertUnauthorized()
            ->assertJson(['success' => false]);

        $this->assertGuest();
    }

    public function test_expired_firebase_id_token_is_rejected(): void
    {
        $this->postJson(route('customer.phone-login'), ['id_token' => $this->token('+919876543210', time() - 5)])
            ->assertUnauthorized()
            ->assertJson(['success' => false]);

        $this->assertGuest();
    }

    public function test_unknown_customer_phone_does_not_create_an_account_or_log_in(): void
    {
        $before = User::count();

        $this->postJson(route('customer.phone-login'), ['id_token' => $this->token('+919876543210')])
            ->assertNotFound()
            ->assertJsonFragment(['message' => 'No customer account was found for this mobile number. Please contact the store for help.']);

        $this->assertSame($before, User::count());
        $this->assertGuest();
    }

    public function test_verified_existing_customer_is_logged_in_and_session_id_rotates(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '+91 98765-43210',
        ]);
        $oldSessionId = session()->getId();

        $this->postJson(route('customer.phone-login'), [
            'id_token' => $this->token('+919876543210'),
            'return_to' => 'my-orders',
        ])
            ->assertOk()
            ->assertJson(['success' => true, 'redirect_url' => route('customer.my-orders')]);

        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertSame('9876543210', session('customer_phone'));
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    private function token(string $phone, ?int $expiresAt = null): string
    {
        $now = time();

        return JWT::encode([
            'iss' => 'https://securetoken.google.com/quara-wardrobe',
            'aud' => 'quara-wardrobe',
            'auth_time' => $now - 5,
            'user_id' => 'firebase-user-123',
            'sub' => 'firebase-user-123',
            'iat' => $now - 5,
            'exp' => $expiresAt ?? $now + 3600,
            'phone_number' => $phone,
            'firebase' => ['sign_in_provider' => 'phone'],
        ], $this->privateKey, 'RS256', 'test-key-id');
    }
}
