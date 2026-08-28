<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPaymentCheckTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('role')->default('customer');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_admin_can_create_and_verify_one_rupee_razorpay_test_payment(): void
    {
        config([
            'services.razorpay.key' => 'rzp_test_example123',
            'services.razorpay.secret' => 'test-secret',
        ]);

        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_admin_test_123',
                'amount' => 100,
                'currency' => 'INR',
            ]),
            'api.razorpay.com/v1/payments/pay_admin_test_123' => Http::response([
                'id' => 'pay_admin_test_123',
                'order_id' => 'order_admin_test_123',
                'amount' => 100,
                'currency' => 'INR',
                'status' => 'captured',
            ]),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.payment-check.order'), ['amount' => 1])
            ->assertOk()
            ->assertJson([
                'key' => 'rzp_test_example123',
                'order_id' => 'order_admin_test_123',
                'amount' => 100,
                'currency' => 'INR',
            ]);

        $signature = hash_hmac('sha256', 'order_admin_test_123|pay_admin_test_123', 'test-secret');

        $this->postJson(route('admin.payment-check.verify'), [
            'razorpay_payment_id' => 'pay_admin_test_123',
            'razorpay_order_id' => 'order_admin_test_123',
            'razorpay_signature' => $signature,
        ])
            ->assertOk()
            ->assertJson([
                'payment_id' => 'pay_admin_test_123',
                'status' => 'captured',
                'amount' => 100,
            ]);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.razorpay.com/v1/orders'
                && $request['amount'] === 100
                && $request['currency'] === 'INR'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('rzp_test_example123:test-secret'));
        });
    }

    public function test_payment_check_rejects_missing_credentials(): void
    {
        config([
            'services.razorpay.key' => null,
            'services.razorpay.secret' => null,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.payment-check.order'), ['amount' => 1])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Razorpay Key ID or Secret Key is not configured correctly. Save both in Master Settings first.']);

        Http::assertNothingSent();
    }

    public function test_payment_check_rejects_an_invalid_callback_signature(): void
    {
        config([
            'services.razorpay.key' => 'rzp_test_example123',
            'services.razorpay.secret' => 'test-secret',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession([
                'razorpay_payment_check' => [
                    'order_id' => 'order_admin_test_123',
                    'amount' => 100,
                    'created_at' => now()->timestamp,
                ],
            ])
            ->postJson(route('admin.payment-check.verify'), [
                'razorpay_payment_id' => 'pay_admin_test_123',
                'razorpay_order_id' => 'order_admin_test_123',
                'razorpay_signature' => 'invalid-signature',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Payment signature verification failed. The Razorpay credentials may not match.']);

        Http::assertNothingSent();
    }

    public function test_non_admin_cannot_access_payment_check(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->postJson(route('admin.payment-check.order'), ['amount' => 1])
            ->assertRedirect(route('admin.login'));
    }
}
