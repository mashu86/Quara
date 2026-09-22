<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('size_masters')) {
            Schema::create('size_masters', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('size_master_rows')) {
            Schema::create('size_master_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('size_master_id')->constrained('size_masters')->onDelete('cascade');
                $table->string('size_label', 50);
                $table->string('chest', 50)->nullable();
                $table->string('waist', 50)->nullable();
                $table->string('length', 50)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('products', 'display_size_chart')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('display_size_chart')->default(false)->after('booked_by');
            });
        }

        if (!Schema::hasColumn('products', 'size_master_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('size_master_id')->nullable()->constrained('size_masters')->nullOnDelete()->after('display_size_chart');
            });
        }

        // Seed initial Size Masters if table is empty
        if (DB::table('size_masters')->count() === 0) {
            $initialCategories = [
                [
                    'name' => 'Korean Ladies Shirt',
                    'slug' => 'korean-ladies-shirt',
                    'sort_order' => 1,
                    'rows' => [
                        ['size_label' => 'XS',  'chest' => '34"', 'waist' => '32"', 'length' => '24–25"'],
                        ['size_label' => 'S',   'chest' => '36"', 'waist' => '34"', 'length' => '25–26"'],
                        ['size_label' => 'M',   'chest' => '38"', 'waist' => '36"', 'length' => '26–27"'],
                        ['size_label' => 'L',   'chest' => '40"', 'waist' => '38"', 'length' => '27–28"'],
                        ['size_label' => 'XL',  'chest' => '42"', 'waist' => '40"', 'length' => '28–29"'],
                        ['size_label' => 'XXL', 'chest' => '44"', 'waist' => '42"', 'length' => '29–30"'],
                        ['size_label' => '3XL', 'chest' => '46"', 'waist' => '44"', 'length' => '30"'],
                        ['size_label' => '4XL', 'chest' => '48"', 'waist' => '46"', 'length' => '30–31"'],
                        ['size_label' => '5XL', 'chest' => '50"', 'waist' => '48"', 'length' => '31–32"'],
                    ]
                ],
                [
                    'name' => 'Korean Crop Top',
                    'slug' => 'korean-crop-top',
                    'sort_order' => 2,
                    'rows' => [
                        ['size_label' => 'XS',  'chest' => '32"', 'waist' => '28"', 'length' => '14–16"'],
                        ['size_label' => 'S',   'chest' => '34"', 'waist' => '30"', 'length' => '15–17"'],
                        ['size_label' => 'M',   'chest' => '36"', 'waist' => '32"', 'length' => '16–18"'],
                        ['size_label' => 'L',   'chest' => '38"', 'waist' => '34"', 'length' => '17–19"'],
                        ['size_label' => 'XL',  'chest' => '40"', 'waist' => '36"', 'length' => '18–20"'],
                        ['size_label' => 'XXL', 'chest' => '42"', 'waist' => '38"', 'length' => '19–21"'],
                        ['size_label' => '3XL', 'chest' => '44"', 'waist' => '40"', 'length' => '20–22"'],
                        ['size_label' => '4XL', 'chest' => '46"', 'waist' => '42"', 'length' => '21–23"'],
                        ['size_label' => '5XL', 'chest' => '48"', 'waist' => '44"', 'length' => '22–24"'],
                    ]
                ],
                [
                    'name' => 'Korean Top',
                    'slug' => 'korean-top',
                    'sort_order' => 3,
                    'rows' => [
                        ['size_label' => 'XS',  'chest' => '32–34"', 'waist' => '28–30"', 'length' => '20–22"'],
                        ['size_label' => 'S',   'chest' => '34–36"', 'waist' => '30–32"', 'length' => '21–23"'],
                        ['size_label' => 'M',   'chest' => '36–38"', 'waist' => '32–34"', 'length' => '22–24"'],
                        ['size_label' => 'L',   'chest' => '38–40"', 'waist' => '34–36"', 'length' => '23–25"'],
                        ['size_label' => 'XL',  'chest' => '40–42"', 'waist' => '36–38"', 'length' => '24–26"'],
                        ['size_label' => 'XXL', 'chest' => '42–44"', 'waist' => '38–40"', 'length' => '25–27"'],
                        ['size_label' => '3XL', 'chest' => '44–46"', 'waist' => '40–42"', 'length' => '26–28"'],
                        ['size_label' => '4XL', 'chest' => '46–48"', 'waist' => '42–44"', 'length' => '27–29"'],
                        ['size_label' => '5XL', 'chest' => '48–50"', 'waist' => '44–46"', 'length' => '28–30"'],
                    ]
                ],
                [
                    'name' => 'Ladies T-Shirt',
                    'slug' => 'ladies-t-shirt',
                    'sort_order' => 4,
                    'rows' => [
                        ['size_label' => 'XS',  'chest' => '32–34"', 'waist' => '28–30"', 'length' => '24–25"'],
                        ['size_label' => 'S',   'chest' => '34–36"', 'waist' => '30–32"', 'length' => '25–26"'],
                        ['size_label' => 'M',   'chest' => '36–38"', 'waist' => '32–34"', 'length' => '26–27"'],
                        ['size_label' => 'L',   'chest' => '38–40"', 'waist' => '34–36"', 'length' => '27–28"'],
                        ['size_label' => 'XL',  'chest' => '40–42"', 'waist' => '36–38"', 'length' => '28–29"'],
                        ['size_label' => 'XXL', 'chest' => '42–44"', 'waist' => '38–40"', 'length' => '29–30"'],
                        ['size_label' => '3XL', 'chest' => '44–46"', 'waist' => '40–42"', 'length' => '30–31"'],
                        ['size_label' => '4XL', 'chest' => '46–48"', 'waist' => '42–44"', 'length' => '31–32"'],
                        ['size_label' => '5XL', 'chest' => '48–50"', 'waist' => '44–46"', 'length' => '32–33"'],
                    ]
                ],
                [
                    'name' => 'Ladies Overcoat / Long Coat',
                    'slug' => 'ladies-overcoat-long-coat',
                    'sort_order' => 5,
                    'rows' => [
                        ['size_label' => 'XS',  'chest' => '34–36"', 'waist' => '30–32"', 'length' => '30–32"'],
                        ['size_label' => 'S',   'chest' => '36–38"', 'waist' => '32–34"', 'length' => '31–33"'],
                        ['size_label' => 'M',   'chest' => '38–40"', 'waist' => '34–36"', 'length' => '32–34"'],
                        ['size_label' => 'L',   'chest' => '40–42"', 'waist' => '36–38"', 'length' => '33–35"'],
                        ['size_label' => 'XL',  'chest' => '42–44"', 'waist' => '38–40"', 'length' => '34–36"'],
                        ['size_label' => 'XXL', 'chest' => '44–46"', 'waist' => '40–42"', 'length' => '35–37"'],
                        ['size_label' => '3XL', 'chest' => '46–48"', 'waist' => '42–44"', 'length' => '36–38"'],
                        ['size_label' => '4XL', 'chest' => '48–50"', 'waist' => '44–46"', 'length' => '37–39"'],
                        ['size_label' => '5XL', 'chest' => '50–52"', 'waist' => '46–48"', 'length' => '38–40"'],
                    ]
                ],
            ];

            foreach ($initialCategories as $catData) {
                $rows = $catData['rows'];
                unset($catData['rows']);
                $catData['created_at'] = now();
                $catData['updated_at'] = now();
                $masterId = DB::table('size_masters')->insertGetId($catData);

                foreach ($rows as $index => $r) {
                    DB::table('size_master_rows')->insert([
                        'size_master_id' => $masterId,
                        'size_label' => $r['size_label'],
                        'chest' => $r['chest'],
                        'waist' => $r['waist'],
                        'length' => $r['length'],
                        'sort_order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'size_master_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['size_master_id']);
                $table->dropColumn('size_master_id');
            });
        }

        if (Schema::hasColumn('products', 'display_size_chart')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('display_size_chart');
            });
        }

        Schema::dropIfExists('size_master_rows');
        Schema::dropIfExists('size_masters');
    }
};
