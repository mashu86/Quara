<?php

namespace Tests\Feature;

use App\Models\GeminiApiKey;
use App\Models\SizeMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductImageAutoFillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        GeminiApiKey::query()->update(['is_active' => false]);
        GeminiApiKey::create(['name' => 'Test', 'api_key' => 'test-image-key', 'is_active' => true]);
        Http::preventStrayRequests();
    }

    private function geminiResponse(array $details, string $finishReason = 'STOP'): array
    {
        return ['candidates' => [[
            'finishReason' => $finishReason,
            'content' => ['parts' => [['text' => json_encode($details)]]],
        ]]];
    }

    private function details(): array
    {
        return ['name' => 'Blue Floral Top', 'description' => 'A blue floral top with short sleeves.',
            'size_master_id' => SizeMaster::where('slug', 'korean-top')->firstOrFail()->id];
    }

    public function test_incomplete_success_is_retried_on_the_same_model_until_name_and_description_exist(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push($this->geminiResponse(['description' => 'A floral top.']))
            ->push($this->geminiResponse($this->details()))]);

        $this->postJson(route('admin.products.ai-auto-fill'), ['image' => UploadedFile::fake()->image('top.jpg')])
            ->assertOk()->assertJson(['success' => true] + $this->details());

        Http::assertSentCount(2);
        $requests = Http::recorded();
        $this->assertSame($requests[0][0]->url(), $requests[1][0]->url());
        $this->assertSame(['name', 'description', 'size_master_id'], $requests[0][0]['generationConfig']['responseSchema']['required']);
        $this->assertSame(4096, $requests[1][0]['generationConfig']['maxOutputTokens']);
    }

    public function test_final_json_is_read_across_parts_and_thought_text_is_ignored(): void
    {
        $json = json_encode($this->details());
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [[
            'finishReason' => 'STOP',
            'content' => ['parts' => [
                ['thought' => true, 'text' => 'This is a thought summary, not JSON.'],
                ['text' => "```json\n".substr($json, 0, 20)],
                ['text' => substr($json, 20)."\n```"],
            ]],
        ]]])]);
        $this->postJson(route('admin.products.ai-auto-fill'), ['image' => UploadedFile::fake()->image('top.jpg')])
            ->assertOk()->assertJson(['success' => true] + $this->details());
        Http::assertSentCount(1);
    }

    public function test_truncated_generation_is_retried_even_if_its_json_parses(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push($this->geminiResponse($this->details(), 'MAX_TOKENS'))
            ->push($this->geminiResponse($this->details()))]);
        $this->postJson(route('admin.products.ai-auto-fill'), ['image' => UploadedFile::fake()->image('top.jpg')])
            ->assertOk()->assertJsonPath('name', 'Blue Floral Top');
        Http::assertSentCount(2);
    }

    public function test_invisible_name_never_returns_success_or_caches_an_incomplete_model(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse([
            'name' => "\u{200B}\u{00A0}", 'description' => 'A floral top.',
        ]))]);
        $this->postJson(route('admin.products.ai-auto-fill'), ['image' => UploadedFile::fake()->image('top.jpg')])
            ->assertStatus(502)->assertJsonPath('success', false)
            ->assertJsonPath('message', fn ($message) => str_contains($message, 'incomplete product details'));
        $this->assertNull(Cache::get('gemini_working_model_'.hash('sha256', 'test-image-key')));
        Http::assertSentCount(10);
    }

    public function test_quota_errors_are_not_retried_as_incomplete_responses(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Quota']], 429)]);
        $this->postJson(route('admin.products.ai-auto-fill'), ['image' => UploadedFile::fake()->image('top.jpg')])
            ->assertStatus(429)->assertJsonPath('success', false);
        Http::assertSentCount(1);
    }
}
