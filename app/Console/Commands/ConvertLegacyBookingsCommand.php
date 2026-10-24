<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductSize;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Old bookings locked a whole product: every piece of every size, including stock added later.
 * This turns each into a booking of 1 piece of one size, so the rest can be sold or booked.
 */
class ConvertLegacyBookingsCommand extends Command
{
    protected $signature = 'bookings:convert-legacy {--dry-run : Only list what would change}';

    protected $description = 'Turn old whole-product bookings into 1-piece size bookings';

    public function handle(): int
    {
        $legacyBooked = Product::with('sizes')
            ->where('is_out_of_stock', true)
            ->where(fn ($q) => $q->where(fn ($q) => $q->whereNotNull('booked_by')->whereRaw("TRIM(booked_by) <> ''"))
                ->orWhereNotNull('booking_type'))
            ->get();

        $converted = [];
        $skipped = [];
        foreach ($legacyBooked as $product) {
            $size = $this->bookedSize($product);
            $who = trim((string) $product->booked_by) ?: 'Business WhatsApp';
            if (! $size) {
                $skipped[] = [$product->product_code ?: '#'.$product->id, $product->name, $who,
                    'Several sizes in stock: unbook it and book the right size by hand'];
                continue;
            }
            $converted[] = [$product->product_code ?: '#'.$product->id, $product->name, $who, $size->size.' × 1'];
            if ($this->option('dry-run')) {
                continue;
            }

            DB::transaction(function () use ($product, $size) {
                ProductBooking::create([
                    'product_id' => $product->id,
                    'product_size_id' => $size->id,
                    'quantity' => 1,
                    'booked_by' => $product->booking_type === 'business_whatsapp' ? null : (trim((string) $product->booked_by) ?: null),
                    'booking_type' => $product->booking_type ?: 'whatsapp_customer',
                    'booking_date' => ($product->booking_date ?? $product->updated_at ?? now())->toDateString(),
                    'status' => 'active',
                ]);
                $product->update([
                    'booked_by' => null, 'booking_type' => null, 'booking_date' => null,
                    'is_out_of_stock' => $product->sizes->sum('stock') <= 0,
                ]);
            });
        }

        $headers = ['Code', 'Product', 'Booked by', 'Result'];
        $this->info(($this->option('dry-run') ? 'Would convert' : 'Converted').' '.count($converted).' booking(s):');
        $this->table($headers, $converted);
        if ($skipped) {
            $this->warn('Left unchanged ('.count($skipped).'): which size was booked is unclear.');
            $this->table($headers, $skipped);
        }

        return self::SUCCESS;
    }

    /** The only size it can be: the product's single size, or the one size that has stock. */
    private function bookedSize(Product $product): ?ProductSize
    {
        if ($product->sizes->count() === 1) {
            return $product->sizes->first();
        }
        $inStock = $product->sizes->where('stock', '>', 0);

        return $inStock->count() === 1 ? $inStock->first() : null;
    }
}
