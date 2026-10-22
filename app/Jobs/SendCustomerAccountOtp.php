<?php

namespace App\Jobs;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCustomerAccountOtp
{
    public function __construct(
        public string $email,
        public string $code,
        public string $purpose = 'Sign-in',
    ) {}

    public function handle(): void
    {
        try {
            $siteName = Setting::get('site_name', config('app.name'));
            $sentAt = now()->format('d M Y, h:i:s A');

            Mail::send('emails.customer_account_otp', [
                'siteName' => $siteName,
                'purpose' => $this->purpose,
                'code' => $this->code,
                'sentAt' => $sentAt,
                'logoPath' => Setting::logoPath(),
            ], function ($message) use ($siteName) {
                $message->to($this->email)->subject($siteName.' - Your '.$this->purpose.' Code - '.now()->format('H:i:s'));
            });
        } catch (\Throwable $exception) {
            Log::error('Customer account OTP email delivery failed.', [
                'recipient_hash' => hash('sha256', strtolower($this->email)),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
