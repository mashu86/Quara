<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VisualSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->string('discount_type')->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->foreignId('product_id');
        });
    }

    public function test_it_returns_catalog_images_for_browser_visual_verification_without_an_ai_key(): void
    {
        config()->set('services.openai.api_key', null);

        $category = Category::create([
            'name' => 'Tops',
            'slug' => 'tops',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Blue Korean Top',
            'slug' => 'blue-korean-top',
            'price' => 499,
            'discount_type' => 'none',
            'discount_value' => 0,
            'description' => 'Blue patterned top',
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'storage/products/top.png',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        $response = $this->post(route('visual.search'), [
            'image' => UploadedFile::fake()->createWithContent('dress.png', $png),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('matching_mode', 'browser_visual')
            ->assertJsonPath('client_visual_verification', true)
            ->assertJsonPath('total_matches', 0)
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonMissingPath('products.0.match_score');
    }

    public function test_it_rejects_non_image_uploads(): void
    {
        $response = $this->post(route('visual.search'), [
            'image' => UploadedFile::fake()->create('not-an-image.txt', 1, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('image');
    }
}
