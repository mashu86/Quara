<?php

namespace Tests\Feature;

use App\Mail\AdminPasswordResetMail;
use App\Models\Setting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('settings');
        Schema::dropIfExists('password_reset_tokens');
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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_admin_can_save_global_email_and_branding_settings(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_name' => 'New Store',
            'site_logo' => UploadedFile::fake()->createWithContent('logo.png', $png),
            'site_favicon' => UploadedFile::fake()->createWithContent('favicon.png', $png),
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_password' => 'app-password-value',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'new@example.com',
            'mail_from_name' => 'New Store Mail',
            'razorpay_key' => 'rzp_test_dynamic123',
            'razorpay_secret' => 'razorpay-secret-value',
            'razorpay_fee_percent' => 2.5,
            'razorpay_gst_percent' => 18,
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $this->assertSame('New Store', Setting::get('site_name'));
        $this->assertSame('new@example.com', Setting::get('mail_from_address'));
        $this->assertSame('new@example.com', Setting::get('mail_username'));
        $this->assertSame('new@example.com', $admin->fresh()->email);
        $this->assertSame('app-password-value', Setting::decryptSecret(Setting::get('mail_password')));
        $this->assertNotSame('app-password-value', Setting::get('mail_password'));
        $this->assertSame('rzp_test_dynamic123', Setting::get('razorpay_key'));
        $this->assertSame('razorpay-secret-value', Setting::decryptSecret(Setting::get('razorpay_secret')));
        $this->assertNotSame('razorpay-secret-value', Setting::get('razorpay_secret'));
        Storage::disk('public')->assertExists(Setting::get('site_logo'));
        Storage::disk('public')->assertExists(Setting::get('site_favicon'));
        $this->assertStringContainsString('/media/settings/branding/', Setting::logoUrl());
        $this->assertStringContainsString('/media/settings/branding/', Setting::faviconUrl());
    }

    public function test_blank_password_keeps_the_existing_encrypted_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::set('mail_password', encrypt('existing-password'), 'mail');
        $savedPassword = Setting::get('mail_password');

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_name' => 'New Store',
            'mail_host' => 'smtp.example.com',
            'mail_port' => 465,
            'mail_password' => '',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'new@example.com',
            'mail_from_name' => 'New Store Mail',
            'razorpay_key' => 'rzp_test_dynamic123',
            'razorpay_fee_percent' => 2,
            'razorpay_gst_percent' => 18,
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertSame($savedPassword, Setting::get('mail_password'));
    }

    public function test_gmail_port_587_cannot_be_saved_without_starttls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_name' => 'New Store',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_password' => '',
            'mail_encryption' => 'none',
            'mail_from_address' => 'new@gmail.com',
            'mail_from_name' => 'New Store Mail',
            'razorpay_key' => 'rzp_test_dynamic123',
            'razorpay_fee_percent' => 2,
            'razorpay_gst_percent' => 18,
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertSame('tls', Setting::get('mail_encryption'));
    }

    public function test_runtime_gmail_transport_requires_starttls(): void
    {
        Setting::set('mail_host', 'smtp.gmail.com', 'mail');
        Setting::set('mail_port', '587', 'mail');
        Setting::set('mail_encryption', 'none', 'mail');
        Setting::set('mail_username', 'sender@gmail.com', 'mail');
        Setting::set('mail_from_address', 'sender@gmail.com', 'mail');
        Setting::set('mail_from_name', 'Sender', 'mail');

        (new AppServiceProvider($this->app))->boot();

        $transport = $this->app->make(MailManager::class)->mailer('smtp')->getSymfonyTransport();

        $this->assertTrue($transport->isAutoTls());
        $this->assertTrue($transport->isTlsRequired());
        $this->assertSame('smtp', config('mail.mailers.smtp.scheme'));
    }

    public function test_runtime_razorpay_config_uses_encrypted_database_credentials(): void
    {
        Setting::set('razorpay_key', 'rzp_live_dynamic456', 'payment');
        Setting::set('razorpay_secret', Crypt::encryptString('database-razorpay-secret'), 'payment');

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('rzp_live_dynamic456', config('services.razorpay.key'));
        $this->assertSame('database-razorpay-secret', config('services.razorpay.secret'));
        $this->assertNotSame('database-razorpay-secret', Setting::get('razorpay_secret'));
    }

    public function test_admin_reset_link_and_form_use_the_new_master_email(): void
    {
        Mail::fake();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'old-admin@example.com',
        ]);
        Setting::set('mail_from_address', 'new-master@example.com', 'mail');

        $this->post(route('admin.password.email'), [
            'email' => 'new-master@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'new-master@example.com',
        ]);

        Mail::assertSent(AdminPasswordResetMail::class, function ($mail) {
            return $mail->hasTo('new-master@example.com')
                && $mail->email === 'new-master@example.com';
        });

        (new AppServiceProvider($this->app))->boot();

        $this->get(route('admin.password.reset', [
            'token' => 'sample-token',
            'email' => 'new-master@example.com',
        ]))
            ->assertOk()
            ->assertSee('Configured Recovery Email Address')
            ->assertSee('value="new-master@example.com"', false);
    }
}
