<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::values();

        return view('admin.settings.index', [
            'razorpayFeePercent' => $settings['razorpay_fee_percent'] ?? '2.00',
            'razorpayGstPercent' => $settings['razorpay_gst_percent'] ?? '18.00',
            'siteName' => $settings['site_name'] ?? config('app.name', 'QUARA WARDROBE'),
            'logoUrl' => Setting::logoUrl($settings),
            'faviconUrl' => Setting::faviconUrl($settings),
            'mailHost' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mailPort' => $settings['mail_port'] ?? config('mail.mailers.smtp.port', 587),
            'mailEncryption' => $settings['mail_encryption'] ?? $this->defaultEncryption(),
            'mailFromAddress' => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mailFromName' => $settings['mail_from_name'] ?? config('mail.from.name', config('app.name')),
            'hasSavedMailPassword' => ! empty($settings['mail_password']),
            'razorpayKey' => $settings['razorpay_key'] ?? config('services.razorpay.key'),
            'hasSavedRazorpaySecret' => ! empty($settings['razorpay_secret']),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:12288'],
            'site_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:4096'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_password' => ['nullable', 'string', 'max:1000'],
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'mail_from_address' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()?->getAuthIdentifier()),
            ],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'razorpay_key' => ['required', 'string', 'max:255', 'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/'],
            'razorpay_secret' => ['nullable', 'string', 'max:1000'],
            'razorpay_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'razorpay_gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'recalculate_past_orders' => ['nullable', 'boolean'],
        ]);

        $validated['mail_encryption'] = $this->normalizeMailEncryption(
            $validated['mail_host'],
            (int) $validated['mail_port'],
            $validated['mail_encryption']
        );

        Setting::set('site_name', $validated['site_name'], 'branding');

        if ($request->hasFile('site_logo')) {
            $this->replaceBrandAsset('site_logo', ImageOptimizerService::optimizeAndStore($request->file('site_logo'), 'settings/branding', 'public'));
        }

        if ($request->hasFile('site_favicon')) {
            $this->replaceBrandAsset('site_favicon', ImageOptimizerService::optimizeAndStore($request->file('site_favicon'), 'settings/branding', 'public'));
        }

        foreach (['mail_host', 'mail_port', 'mail_encryption', 'mail_from_address', 'mail_from_name'] as $key) {
            Setting::set($key, $validated[$key], 'mail');
        }

        // One master email is used for SMTP login, From address, support, and recovery.
        Setting::set('mail_username', $validated['mail_from_address'], 'mail');

        if ($request->user()?->isAdmin() && $request->user()->email !== $validated['mail_from_address']) {
            $request->user()->update(['email' => $validated['mail_from_address']]);
        }

        if (! empty($validated['mail_password'])) {
            Setting::set('mail_password', Crypt::encryptString($validated['mail_password']), 'mail');
        }

        Setting::set('razorpay_key', $validated['razorpay_key'], 'payment');

        if (! empty($validated['razorpay_secret'])) {
            Setting::set('razorpay_secret', Crypt::encryptString($validated['razorpay_secret']), 'payment');
        }

        Setting::set('razorpay_fee_percent', number_format((float) $validated['razorpay_fee_percent'], 2, '.', ''), 'payment');
        Setting::set('razorpay_gst_percent', number_format((float) $validated['razorpay_gst_percent'], 2, '.', ''), 'payment');

        if ($request->boolean('recalculate_past_orders')) {
            $feePercent = (float) $validated['razorpay_fee_percent'];
            $gstPercent = (float) $validated['razorpay_gst_percent'];

            Order::where('payment_method', 'online')->eachById(function (Order $order) use ($feePercent, $gstPercent) {
                $order->calculateRazorpayCharge(null, $feePercent, $gstPercent);
            });
        }

        return redirect()->route('admin.settings.index')->with('success', 'Master settings updated successfully. Branding and email configuration are now active site-wide.');
    }

    private function replaceBrandAsset(string $settingKey, string $newPath): void
    {
        $oldPath = Setting::get($settingKey);
        Setting::set($settingKey, $newPath, 'branding');

        if ($oldPath && str_starts_with($oldPath, 'settings/branding/') && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    private function defaultEncryption(): string
    {
        $scheme = config('mail.mailers.smtp.scheme');

        if ($scheme === 'smtps' || (int) config('mail.mailers.smtp.port') === 465) {
            return 'ssl';
        }

        return config('mail.mailers.smtp.encryption') ?: 'tls';
    }

    private function normalizeMailEncryption(string $host, int $port, string $encryption): string
    {
        if (str_contains(strtolower($host), 'gmail.com')) {
            return $port === 465 ? 'ssl' : 'tls';
        }

        return $encryption;
    }
}
