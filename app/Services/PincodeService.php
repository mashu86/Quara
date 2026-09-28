<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PincodeService
{
    private static ?array $directory = null;

    public function lookup(string $pincode): array
    {
        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            throw ValidationException::withMessages(['pin_code' => 'Enter a valid six-digit PIN code.']);
        }

        self::$directory ??= json_decode(file_get_contents(public_path('data/pincodes.json')), true, 512, JSON_THROW_ON_ERROR);
        $index = self::$directory['pins'][$pincode] ?? null;
        if ($index !== null) {
            [$district, $state] = self::$directory['locations'][$index];

            return ['district' => app(DistrictOfferService::class)->canonicalDistrict($district), 'state' => $state];
        }

        return Cache::rememberForever('verified-pincode:'.$pincode, function () use ($pincode) {
            try {
                // Use HTTP/1.1 for compatibility with the postal lookup service.
                $response = Http::withOptions(['version' => 1.1])
                    ->withUserAgent('Quara/1.0 (Postal address lookup)')
                    ->connectTimeout(3)->timeout(8)->get('https://api.postalpincode.in/pincode/'.$pincode)->throw();
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['pin_code' => 'PIN lookup is temporarily unavailable. Please retry before placing your order.']);
            }
            $data = $response->json('0');
            $offices = collect($data['PostOffice'] ?? []);
            $locations = $offices->filter(fn ($office) => !empty($office['District']) && !empty($office['State']))
                ->map(fn ($office) => [
                    'district' => app(DistrictOfferService::class)->canonicalDistrict($office['District']),
                    'state' => trim($office['State']),
                ])->unique(fn ($location) => strtolower($location['state'].'|'.$location['district']))->values();
            if (($data['Status'] ?? '') !== 'Success' || $locations->count() !== 1) {
                throw ValidationException::withMessages(['pin_code' => 'This PIN code could not be matched to one district. Please check your delivery PIN code.']);
            }

            return $locations->first();
        });
    }
}
