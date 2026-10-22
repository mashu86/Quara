<?php

namespace App\Jobs;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCustomerAccountOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $code,
        public string $purpose = 'Sign-in',
    ) {
        $this->onConnection('deferred');
    }

    public function handle(): void
    {
        try {
            $siteName = Setting::get('site_name', config('app.name'));
            Mail::raw("Your {$siteName} verification code is {$this->code}. It expires in 5 minutes.", function ($message) use ($siteName) {
                $message->to($this->email)->subject($siteName.' - Your '.$this->purpose.' Code');
            });
        } catch (\Throwable $exception) {
            Log::error('Customer account OTP email delivery failed.', [
                'recipient_hash' => hash('sha256', strtolower($this->email)),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
