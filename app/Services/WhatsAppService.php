<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    /**
     * Send the approved order-confirmation template to the customer.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        if (! config('services.whatsapp.enabled')) {
            return false;
        }

        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');
        $recipient = $this->normalizePhoneNumber($order->customer_phone);

        if (! $phoneNumberId || ! $accessToken || ! $recipient) {
            Log::warning('WhatsApp order confirmation was not sent because its configuration or customer phone number is invalid.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return false;
        }

        $apiVersion = config('services.whatsapp.api_version', 'v23.0');
        $templateName = config('services.whatsapp.order_confirmation_template', 'order_confirmation');
        $languageCode = config('services.whatsapp.template_language', 'en');

        try {
            $response = Http::asJson()
                ->withToken($accessToken)
                ->timeout(10)
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipient,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => $languageCode],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $order->customer_name],
                                    ['type' => 'text', 'text' => $order->order_number],
                                    ['type' => 'text', 'text' => 'INR '.number_format((float) $order->grand_total, 2, '.', ',')],
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('WhatsApp rejected an order confirmation.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (Throwable $exception) {
            Log::error('WhatsApp order confirmation request failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);
        }

        return false;
    }

    private function normalizePhoneNumber(?string $phoneNumber): ?string
    {
        if (! $phoneNumber) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber);

        if (! $digits) {
            return null;
        }

        if (strlen($digits) === 10) {
            $countryCode = preg_replace('/\D+/', '', (string) config('services.whatsapp.default_country_code', '91'));
            $digits = $countryCode.$digits;
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $countryCode = preg_replace('/\D+/', '', (string) config('services.whatsapp.default_country_code', '91'));
            $digits = $countryCode.substr($digits, 1);
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? $digits : null;
    }
}
