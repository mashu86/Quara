<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Body-size reference, not finished garment measurements.
        // XS through 3XL: https://labeldc.com/pages/size-guide
        // XXS, 4XL and 5XL extend the shop reference in two-inch steps.
        $categories = [
            'korean-ladies-shirt' => 'Ladies Shirt',
            'korean-crop-top' => 'Crop Top',
            'korean-top' => 'Korean Top',
            'ladies-t-shirt' => 'Ladies T-Shirt',
            'ladies-overcoat-long-coat' => 'Ladies Overcoat / Long Coat',
            'ladies-sweater' => 'Ladies Sweater',
        ];
        $labels = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL'];

        DB::transaction(function () use ($categories, $labels) {
            foreach ($categories as $slug => $name) {
                $master = DB::table('size_masters')->where('slug', $slug)->first();
                if ($master) {
                    $id = $master->id;
                    DB::table('size_masters')->where('id', $id)->update(['name' => $name, 'updated_at' => now()]);
                } else {
                    $id = DB::table('size_masters')->insertGetId([
                        'name' => $name, 'slug' => $slug,
                        'sort_order' => (DB::table('size_masters')->max('sort_order') ?? 0) + 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                foreach ($labels as $index => $label) {
                    $key = ['size_master_id' => $id, 'size_label' => $label];
                    $values = [
                        'chest' => (string) (30 + $index * 2),
                        'waist' => (string) (24 + $index * 2),
                        'length' => null, 'sort_order' => $index + 1, 'updated_at' => now(),
                    ];
                    if (DB::table('size_master_rows')->where($key)->exists()) {
                        DB::table('size_master_rows')->where($key)->update($values);
                    } else {
                        DB::table('size_master_rows')->insert($key + $values + ['created_at' => now()]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Data correction: retain edited measurements on schema rollback.
    }
};
