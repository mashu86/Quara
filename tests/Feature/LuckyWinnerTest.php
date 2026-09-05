<?php

namespace Tests\Feature;

use App\Models\LuckyDraw;
use App\Models\Order;
use App\Models\OrderOperation;
use App\Models\User;
use App\Services\LuckyWinnerDrafts;
use App\Services\LuckyWinnerEligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LuckyWinnerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();
        config(['luckywinner.cache_store' => 'array']);
        $this->travelTo(now()->setDate(2026, 9, 5)->setTime(12, 0));
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function order(array $overrides = []): Order
    {
        return Order::forceCreate(array_merge([
            'order_number' => 'LW-TEST-'.++$this->sequence,
            'customer_name' => 'Customer '.$this->sequence, 'customer_phone' => '980000'.str_pad((string) $this->sequence, 4, '0', STR_PAD_LEFT),
            'customer_email' => 'customer'.$this->sequence.'@example.test',
            'house_building' => 'Test House', 'street' => 'Test Road', 'area' => 'Test Area',
            'city' => 'Kannur', 'district' => 'Kannur', 'state' => 'Kerala', 'pin_code' => '670001',
            'subtotal' => 1000, 'grand_total' => 1000, 'payment_method' => 'online',
            'order_status' => 'confirmed', 'payment_status' => 'paid',
            'created_at' => '2026-09-05 12:00:00',
        ], $overrides));
    }

    private function prepare(array $input = []): array
    {
        return $this->actingAs($this->admin)->postJson(route('luckywinner.prepare'), $input ?: [
            'draw_type' => 'range', 'start_date' => '2026-09-05', 'end_date' => '2026-09-05',
        ])->assertOk()->json();
    }

    public function test_all_draw_endpoints_require_admin_and_login_returns_to_studio(): void
    {
        $this->get(route('luckywinner.index'))->assertRedirect(route('admin.login'));
        $this->assertSame(route('luckywinner.index'), session('url.intended'));
        $this->postJson(route('luckywinner.prepare'), [])->assertUnauthorized();
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('luckywinner.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('luckywinner.history'))->assertForbidden();
        $this->actingAs($customer)->postJson(route('luckywinner.select', 'ae2edb2f-bc4b-444b-8444-21c0aeececb2'), ['gift_count' => 1, 'position' => 1])->assertForbidden();
        $this->actingAs($customer)->postJson(route('luckywinner.store', 'ae2edb2f-bc4b-444b-8444-21c0aeececb2'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('luckywinner.index'))->assertOk()->assertSee('Lucky')->assertSee('September 2026')->assertSee('June 2026');
    }

    public function test_same_day_includes_both_boundaries_and_keeps_separate_orders_for_same_customer(): void
    {
        $first = $this->order(['created_at' => '2026-09-05 00:00:00', 'customer_name' => 'Same Customer', 'customer_phone' => '9876543210']);
        $last = $this->order(['created_at' => '2026-09-05 23:59:59', 'customer_name' => 'Same Customer', 'customer_phone' => '9876543210', 'order_source' => 'manual', 'payment_method' => 'offline_sale', 'order_status' => 'delivered']);
        $this->order(['created_at' => '2026-09-04 23:59:59']);
        $this->order(['created_at' => '2026-09-06 00:00:00']);
        $this->order(['order_status' => 'pending']);
        $this->order(['payment_status' => 'pending']);
        $this->order(['payment_status' => 'failed']);
        $this->order(['payment_status' => 'refunded']);
        $this->order(['order_status' => 'cancelled']);
        $this->order(['customer_phone' => '+91 95448 32975']);
        $test = $this->order();
        OrderOperation::create(['order_id' => $test->id, 'status' => 'inactive']);
        $draft = $this->prepare();
        $this->assertSame([$first->id, $last->id], array_column($draft['entries'], 'order_id'));
        $this->assertSame(2, $draft['total_entries']);
        $this->assertSame('05 Sep 2026', $draft['period']['period_label']);
        $this->assertArrayHasKey('customer_address', $draft['entries'][0]);
        $this->assertArrayHasKey('masked_phone', $draft['entries'][0]);
        $this->assertArrayNotHasKey('customer_phone', $draft['entries'][0]);
        $this->assertDatabaseCount('lucky_draws', 0);
    }

    public function test_month_custom_range_and_sale_date_use_inclusive_local_dates(): void
    {
        $entry = $this->order(['created_at' => '2026-08-01 12:00:00', 'sale_date' => '2026-09-30 23:59:59']);
        $this->order(['created_at' => '2026-09-05 12:00:00', 'sale_date' => '2026-08-31 12:00:00']);
        $draft = $this->prepare(['draw_type' => 'month', 'month' => '2026-09']);
        $this->assertSame([$entry->id], array_column($draft['entries'], 'order_id'));
        $this->assertSame('2026-09-01', $draft['period']['start_date']);
        $this->assertSame('2026-09-30', $draft['period']['end_date']);
        $this->assertSame('September 2026', $draft['period']['period_label']);
        $draft = $this->prepare(['draw_type' => 'range', 'start_date' => '2026-09-01', 'end_date' => '2026-10-03']);
        $this->assertSame('01 Sep 2026 – 03 Oct 2026', $draft['period']['period_label']);
    }

    public function test_return_activity_reduces_all_matching_customer_entries_only_within_period(): void
    {
        $first = $this->order(['customer_phone' => '9876543210']);
        $second = $this->order(['customer_phone' => '+91 98765 43210']);
        $normal = $this->order();
        $oldOrder = $this->order(['created_at' => '2026-08-01 10:00:00', 'customer_phone' => '9876543210']);
        OrderOperation::forceCreate(['order_id' => $oldOrder->id, 'operation_type' => 'customer_return', 'return_date' => '2026-09-05', 'created_at' => '2026-09-06 10:00:00', 'status' => 'active']);
        OrderOperation::create(['order_id' => $normal->id, 'operation_type' => 'customer_return', 'return_date' => '2026-09-04', 'status' => 'active']);
        OrderOperation::create(['order_id' => $normal->id, 'operation_type' => 'other', 'return_date' => '2026-09-05', 'status' => 'active']);
        $public = $this->prepare();
        $draft = app(LuckyWinnerDrafts::class)->get($public['token'], $this->admin);
        $entries = collect($draft['entries'])->keyBy('order_id');
        $this->assertSame(50, $entries[$first->id]['eligibility']['weight']);
        $this->assertSame(50, $entries[$second->id]['eligibility']['weight']);
        $this->assertSame(100, $entries[$normal->id]['eligibility']['weight']);
        $this->assertSame($oldOrder->id, $entries[$first->id]['eligibility']['return_activity'][0]['order_id']);
        config(['luckywinner.return_weight' => 500]);
        $this->assertSame(100, app(LuckyWinnerEligibility::class)->rules()['return_weight']);
        config(['luckywinner.return_weight' => 0]);
        $this->assertSame(1, app(LuckyWinnerEligibility::class)->rules()['return_weight']);
    }

    public function test_selection_retries_do_not_redraw_and_store_is_idempotent_with_immutable_snapshots(): void
    {
        $first = $this->order(['customer_name' => 'Same Customer', 'customer_phone' => '9876543210']);
        $second = $this->order(['customer_name' => 'Same Customer', 'customer_phone' => '9876543210']);
        $originalOrders = DB::table('orders')->orderBy('id')->get()->toJson();
        $originalPayments = DB::table('payments')->get()->toJson();
        $writes = [];
        DB::listen(function ($event) use (&$writes) {
            if (preg_match('/^\s*(insert|update|delete)/i', $event->sql) && !preg_match('/(?:users|sessions)/i', $event->sql)) {
                $writes[] = $event->sql;
            }
        });
        $draft = $this->prepare();
        $select = route('luckywinner.select', $draft['token']);
        $store = route('luckywinner.store', $draft['token']);
        $this->postJson($store)->assertConflict();
        $one = $this->postJson($select, ['gift_count' => 2, 'position' => 1])->assertOk()->json();
        $retry = $this->postJson($select, ['gift_count' => 2, 'position' => 1])->assertOk()->json();
        $this->assertSame($one['winners'], $retry['winners']);
        $this->postJson($select, ['gift_count' => 1, 'position' => 2])->assertConflict();
        $this->postJson($store)->assertConflict();
        $two = $this->postJson($select, ['gift_count' => 2, 'position' => 2])->assertOk()->json();
        $this->assertEqualsCanonicalizing([$first->id, $second->id], array_column($two['winners'], 'order_id'));
        $this->assertDatabaseCount('lucky_draws', 0);
        $this->assertDatabaseCount('lucky_draw_winners', 0);
        $saved = $this->postJson($store, ['winners' => [['order_id' => 999999]]])->assertOk()->json();
        $this->postJson($store)->assertOk()->assertJson($saved);
        $this->assertDatabaseCount('lucky_draws', 1);
        $this->assertDatabaseCount('lucky_draw_winners', 2);
        $this->assertSame($originalOrders, DB::table('orders')->orderBy('id')->get()->toJson());
        $this->assertSame($originalPayments, DB::table('payments')->get()->toJson());
        file_put_contents('c:/xampp/htdocs/Quara/sql_debug.txt', print_r($writes, true));
        foreach ($writes as $sql) {
            $this->assertMatchesRegularExpression('/(?:into|update)\s+["`]?lucky_draw(?:s|_winners)/i', $sql, 'Failed SQL: '.$sql);
        }
        $this->assertMatchesRegularExpression('/^LW-2026-09-\d{4,}$/', $saved['draw_number']);
        $first->update(['customer_name' => 'Changed Later']);
        $this->actingAs($this->admin)->get($saved['url'])->assertOk()->assertSee('Same Customer')->assertDontSee('Changed Later');
        $this->actingAs($this->admin)->get(route('admin.luckywinner.history'))->assertOk()->assertSee($saved['draw_number']);
        Cache::store('array')->flush();
        $this->postJson($store)->assertOk()->assertJson($saved);
    }

    public function test_large_pool_removes_only_selected_order_and_never_creates_history_early(): void
    {
        for ($i = 0; $i < 151; $i++) {
            $this->order();
        }
        $draft = $this->prepare();
        $this->assertSame(151, $draft['total_entries']);
        $this->assertCount(151, $draft['entries']);
        $selected = $this->postJson(route('luckywinner.select', $draft['token']), ['gift_count' => 3, 'position' => 1])->assertOk()->json();
        $this->assertCount(1, $selected['winners']);
        $this->get(route('luckywinner.index'))->assertOk()->assertSee($draft['token']);
        $this->assertDatabaseCount('lucky_draws', 0);
    }

    public function test_invalid_ranges_empty_pool_invalid_gift_counts_and_expired_drafts(): void
    {
        $this->actingAs($this->admin)->postJson(route('luckywinner.prepare'), ['draw_type' => 'range', 'start_date' => '2026-09-06', 'end_date' => '2026-09-05'])->assertUnprocessable();
        $this->postJson(route('luckywinner.prepare'), ['draw_type' => 'month', 'month' => '2026-13'])->assertUnprocessable();
        $this->postJson(route('luckywinner.prepare'), ['draw_type' => 'month', 'month' => '2026-09'])->assertUnprocessable();
        $this->order();
        $draft = $this->prepare();
        $url = route('luckywinner.select', $draft['token']);
        $this->postJson($url, ['gift_count' => 2, 'position' => 1])->assertUnprocessable();
        $this->postJson($url, ['gift_count' => 0, 'position' => 1])->assertUnprocessable();
        $this->postJson($url, ['gift_count' => 1, 'position' => 2])->assertConflict();
        $this->travel(25)->hours();
        $this->postJson($url, ['gift_count' => 1, 'position' => 1])->assertGone();
    }

    public function test_drafts_are_admin_owned_and_changed_eligibility_requires_reload(): void
    {
        $order = $this->order();
        $draft = $this->prepare();
        $other = User::factory()->create(['role' => 'admin']);
        $this->actingAs($other)->postJson(route('luckywinner.select', $draft['token']), ['gift_count' => 1, 'position' => 1])->assertForbidden();
        $this->actingAs($other)->postJson(route('luckywinner.store', $draft['token']))->assertForbidden();
        $order->update(['order_status' => 'cancelled']);
        $this->actingAs($this->admin)->postJson(route('luckywinner.select', $draft['token']), ['gift_count' => 1, 'position' => 1])->assertConflict();
        $this->assertDatabaseCount('lucky_draws', 0);
    }

    public function test_intentional_new_draw_for_same_period_gets_a_separate_event(): void
    {
        $this->order();
        for ($i = 0; $i < 2; $i++) {
            $draft = $this->prepare();
            $this->postJson(route('luckywinner.select', $draft['token']), ['gift_count' => 1, 'position' => 1])->assertOk();
            $this->postJson(route('luckywinner.store', $draft['token']))->assertOk();
        }
        $this->assertDatabaseCount('lucky_draws', 2);
        $this->assertSame(2, LuckyDraw::distinct()->count('draw_number'));
    }
}
