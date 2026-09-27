<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\SizeMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAutoFillViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_autofill_explains_when_saved_active_key_cannot_be_decrypted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        config(['services.gemini.api_key' => null]);
        $otherEncrypter = new \Illuminate\Encryption\Encrypter(random_bytes(32), 'AES-256-CBC');
        $ciphertext = $otherEncrypter->encryptString('test-key-not-a-real-credential');
        \App\Models\GeminiApiKey::query()->update(['is_active' => false]);
        \App\Models\GeminiApiKey::create(['name' => 'Imported key', 'api_key' => $ciphertext, 'is_active' => true]);
        \App\Models\Setting::set('gemini_api_key', $ciphertext, 'ai');

        $this->postJson(route('admin.products.ai-auto-fill'), [
            'image' => \Illuminate\Http\UploadedFile::fake()->image('dress.jpg'),
        ])->assertStatus(422)->assertJson(['success' => false])
            ->assertJsonPath('message', fn ($message) => str_contains($message, 'cannot be decrypted'));
    }

    public function test_forms_embed_size_charts_and_list_shows_product_timestamps(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $category = Category::create(['name' => 'Tops', 'slug' => 'tops', 'status' => 'active']);
        $master = SizeMaster::firstOrCreate(['slug' => 'korean-top'], ['name' => 'Korean Top', 'sort_order' => 1]);
        $master->rows()->create(['size_label' => 'L', 'chest' => '40', 'waist' => '38', 'length' => '30', 'sort_order' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Floral Top', 'price' => 129, 'status' => 'active']);
        ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock' => 1]);

        foreach ([route('admin.products.create'), route('admin.products.edit', $product)] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('data-chart=', false)
                ->assertSee('Korean Top')
                ->assertSee('js/product-auto-fill.js')
                ->assertSee('Auto Fill Product');
        }
        $this->get(route('admin.products.index'))->assertOk()
            ->assertSee('<th>Created</th>', false)
            ->assertSee('<th>Updated</th>', false)
            ->assertSee($product->created_at->format('d M Y'))
            ->assertSee('aria-controls="instaSettingsDrawer"', false);
    }
}
