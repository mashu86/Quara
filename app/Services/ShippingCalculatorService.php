<?php

namespace App\Services;

class ShippingCalculatorService
{
    public const ORIGIN_PINCODE = '670582';
    public const ORIGIN_LOCATION = 'Naduvil, Kannur, Kerala';

    public function calculateRate(string $destinationPincode = '670582', float $weightKg = 0.30, bool $hasExcludedProduct = true): array
    {
        $cleanPincode = preg_replace('/[^0-9]/', '', $destinationPincode);
        if (strlen($cleanPincode) !== 6) {
            $cleanPincode = '670582';
        }

        if (!$hasExcludedProduct) {
            return [
                'shipping_fee' => 0.00,
                'origin_pincode' => self::ORIGIN_PINCODE,
                'destination_pincode' => $cleanPincode,
                'is_local_kerala' => true,
                'estimated_days' => '1 - 2 Business Days',
                'delivery_type' => 'Included (Free Delivery)',
                'message' => 'Free Shipping Included on this product!',
            ];
        }

        // Check if Kerala (Pincodes starting with 67, 68, 69)
        $isKerala = in_array(substr($cleanPincode, 0, 2), ['67', '68', '69']);
        $weightKg = max(0.1, $weightKg);

        if ($isKerala) {
            // Local Kerala: ₹40 for up to 0.5kg + ₹20 for each extra 0.5kg
            $baseFee = 40.00;
            $extraWeight = max(0, $weightKg - 0.5);
            $extraBlocks = ceil($extraWeight / 0.5);
            $shippingFee = $baseFee + ($extraBlocks * 20.00);
            $estimatedDays = ($cleanPincode === '670582') ? 'Same Day / Next Day' : '1 - 2 Business Days';
        } else {
            // Rest of India: ₹80 for up to 0.5kg + ₹40 for each extra 0.5kg
            $baseFee = 80.00;
            $extraWeight = max(0, $weightKg - 0.5);
            $extraBlocks = ceil($extraWeight / 0.5);
            $shippingFee = $baseFee + ($extraBlocks * 40.00);
            $estimatedDays = '3 - 5 Business Days';
        }

        return [
            'shipping_fee' => round($shippingFee, 2),
            'origin_pincode' => self::ORIGIN_PINCODE,
            'origin_location' => self::ORIGIN_LOCATION,
            'destination_pincode' => $cleanPincode,
            'is_local_kerala' => $isKerala,
            'estimated_days' => $estimatedDays,
            'weight_kg' => $weightKg,
            'delivery_type' => 'Standard Express Courier',
            'message' => "Estimated Delivery: {$estimatedDays} from Naduvil (670582)",
        ];
    }
}
