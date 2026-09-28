<?php

namespace App\Services;

use App\Models\DistrictOffer;
use Carbon\Carbon;

class DistrictOfferService
{
    public function canonicalDistrict(string $district): string
    {
        $key = strtolower(trim(preg_replace('/\s+/', ' ', $district)));
        $key = preg_replace('/\s+district$/', '', $key);
        foreach (config('district_offers.districts') as $name) {
            if (strtolower($name) === $key) {
                return $name;
            }
        }

        return config('district_offers.aliases')[$key] ?? trim($district);
    }

    public function eligible(string $district, string $state, $date): ?DistrictOffer
    {
        if (strcasecmp(trim($state), config('district_offers.state')) !== 0) {
            return null;
        }
        // Select the latest version FIRST: an inactive/expired revision must not revive an older one.
        $offer = DistrictOffer::where('district', $this->canonicalDistrict($district))
            ->where('state', config('district_offers.state'))->orderByDesc('id')->first();
        $day = Carbon::parse($date, 'Asia/Kolkata')->setTimezone('Asia/Kolkata')->toDateString();

        return $offer && $offer->is_active
            && $day >= $offer->start_date->toDateString()
            && $day <= $offer->end_date->toDateString() ? $offer : null;
    }

    public function snapshot(?DistrictOffer $offer, float $base, bool $accepted): array
    {
        $base = max(0, round($base, 2));
        $amount = $offer ? min($base, round($offer->method === 'percentage'
            ? $base * (float) $offer->value / 100 : (float) $offer->value, 2)) : 0;

        return [
            'district_offer_id' => $offer?->id,
            'district_offer_snapshot' => $offer ? [
                'district' => $offer->district, 'state' => $offer->state,
                'method' => $offer->method, 'value' => (float) $offer->value,
                'is_active' => $offer->is_active,
                'start_date' => $offer->start_date->toDateString(),
                'end_date' => $offer->end_date->toDateString(),
                'eligible_amount' => $amount, 'base_amount' => $base,
                'recorded_at' => now('Asia/Kolkata')->toIso8601String(),
            ] : null,
            'district_offer_discount' => $accepted ? $amount : 0,
            'district_offer_accepted' => $offer ? $accepted : null,
        ];
    }
}
