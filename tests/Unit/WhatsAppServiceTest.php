<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public function test_it_sends_the_order_confirmation_template_to_the_customer(): void
    {
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_version' => 'v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'order_confirmation_template' => 'order_confirmation',
            'template_language' => 'en',
            'default_country_code' => '91',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200),
        ]);

        $order = new Order([
            'customer_name' => 'Test Customer',
            'customer_phone' => '98765 43210',
            'grand_total' => 1299.50,
        ]);
        $order->id = 10;
        $order->order_number = 'QW-20260822-00001';

        $sent = app(WhatsAppService::class)->sendOrderConfirmation($order);

        $this->assertTrue($sent);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['to'] === '919876543210'
                && $request['type'] === 'template'
                && $request['template']['name'] === 'order_confirmation'
                && $request['template']['components'][0]['parameters'][0]['text'] === 'Test Customer'
                && $request['template']['components'][0]['parameters'][1]['text'] === 'QW-20260822-00001'
                && $request['template']['components'][0]['parameters'][2]['text'] === 'INR 1,299.50';
        });
    }

    public function test_it_does_not_call_whatsapp_when_the_integration_is_disabled(): void
    {
        config()->set('services.whatsapp.enabled', false);
        Http::fake();

        $order = new Order([
            'customer_name' => 'Test Customer',
            'customer_phone' => '9876543210',
            'grand_total' => 100,
        ]);
        $order->order_number = 'QW-20260822-00002';

        $sent = app(WhatsAppService::class)->sendOrderConfirmation($order);

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }
}
