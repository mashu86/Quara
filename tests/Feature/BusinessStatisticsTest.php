<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ExpenseController;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\User;
use App\Services\BusinessStatistics;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BusinessStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-09-12 12:00:00', 'Asia/Kolkata'));
        config(['business.start_date' => '2026-08-28']);
    }

    private function order(array $overrides = []): Order
    {
        return Order::forceCreate(array_merge([
            'order_number' => 'STATS-'.++$this->sequence,
            'customer_name' => 'Statistics Customer',
            'customer_phone' => '9876543210',
            'house_building' => 'House', 'street' => 'Street', 'area' => 'Area',
            'city' => 'Kannur', 'district' => 'Kannur', 'state' => 'Kerala', 'pin_code' => '670001',
            'subtotal' => 1000, 'grand_total' => 1000, 'payment_method' => 'cod',
            'payment_status' => 'paid', 'order_status' => 'delivered',
            'sale_date' => '2026-09-12 00:00:00',
            'created_at' => '2026-09-12 12:00:00',
        ], $overrides));
    }

    public function test_average_uses_every_business_day_and_excludes_invalid_and_future_sales(): void
    {
        $this->order(['sale_date' => '2026-08-28 00:00:00', 'grand_total' => 600]);
        $this->order(['sale_date' => null, 'created_at' => '2026-09-12 23:59:59']);
        $this->order(['sale_date' => '2026-09-13 00:00:00']);
        $this->order(['sale_date' => '2026-08-27 23:59:59']);
        $this->order(['payment_status' => 'pending']);
        $this->order(['order_status' => 'cancelled']);
        $this->order(['customer_phone' => '9544832975']);
        $inactive = $this->order();
        OrderOperation::create(['order_id' => $inactive->id, 'status' => 'inactive']);

        $report = app(BusinessStatistics::class)->report(['period' => 'all']);

        $this->assertCount(16, $report['days']);
        $this->assertSame(16, $report['businessDays']);
        $this->assertEquals(1600, $report['totalSales']);
        $this->assertEquals(100, $report['averageSales']);
        $this->assertSame('2026-09-12', $report['highest']['date']);
        $this->assertSame('2026-08-29', $report['lowest']['date']);
        $this->assertEquals(0, $report['lowest']['sales']);
        $this->assertSame(14, $report['lowestCount']);
    }

    public function test_expenses_match_existing_business_costs_and_revenue_can_be_negative(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable();
        });
        $category = Category::create(['name' => 'Stats', 'slug' => 'stats', 'status' => 'active']);
        $product = Product::forceCreate(['category_id' => $category->id, 'name' => 'Stats product', 'slug' => 'stats-product', 'price' => 1000, 'final_price' => 1000, 'cost_price' => 200, 'status' => 'active']);
        $order = $this->order(['payment_method' => 'online', 'razorpay_total_charge' => 23.60]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'size' => 'M', 'quantity' => 2, 'unit_price' => 500, 'final_unit_price' => 500, 'subtotal' => 1000, 'item_status' => 'active']);
        DB::table('expenses')->insert(['title' => 'Rent', 'amount' => 300, 'expense_date' => '2026-09-12']);
        $operation = OrderOperation::create(['order_id' => $order->id, 'status' => 'active', 'additional_expense_total' => 50]);
        OrderRefund::create(['order_id' => $order->id, 'order_operation_id' => $operation->id, 'refund_amount' => 100, 'refund_date' => '2026-09-11']);
        $inactive = OrderOperation::create(['order_id' => $this->order()->id, 'status' => 'inactive', 'additional_expense_total' => 999]);
        OrderRefund::create(['order_id' => $inactive->order_id, 'order_operation_id' => $inactive->id, 'refund_amount' => 999, 'refund_date' => '2026-09-11']);
        $report = app(BusinessStatistics::class)->report(['period' => 'range', 'start_date' => '2026-09-11', 'end_date' => '2026-09-12']);

        $this->assertEquals(100, $report['days'][0]['expense']);
        $this->assertEquals(-100, $report['days'][0]['revenue']);
        $this->assertEquals(773.60, $report['days'][1]['expense']);
        $this->assertEquals(226.40, $report['days'][1]['revenue']);
        $this->assertEquals(ExpenseController::getBusinessExpensesSummary('2026-09-11', '2026-09-12')['total'], array_sum(array_column($report['days'], 'expense')));
    }

    public function test_presets_and_inclusive_custom_range_keep_average_independent(): void
    {
        $this->order(['sale_date' => '2026-08-28', 'grand_total' => 600]);
        $this->order();
        foreach (['today' => ['2026-09-12', 1], 'week' => ['2026-09-07', 6], 'month' => ['2026-09-01', 12], 'all' => ['2026-08-28', 16]] as $period => [$start, $count]) {
            $report = app(BusinessStatistics::class)->report(['period' => $period]);
            $this->assertSame($start, $report['startDate']);
            $this->assertCount($count, $report['days']);
            $this->assertEquals(100, $report['averageSales']);
        }
        $report = app(BusinessStatistics::class)->report(['period' => 'range', 'start_date' => '2026-08-28', 'end_date' => '2026-08-28']);
        $this->assertCount(1, $report['days']);
        $this->assertEquals(600, $report['days'][0]['sales']);
        $this->assertSame($report['highest'], $report['lowest']);
    }

    public function test_empty_store_and_first_business_day_are_safe(): void
    {
        $this->travelTo(Carbon::parse('2026-08-28 12:00:00', 'Asia/Kolkata'));
        $report = app(BusinessStatistics::class)->report(['period' => 'all']);
        $this->assertCount(1, $report['days']);
        $this->assertSame(1, $report['businessDays']);
        $this->assertEquals(0, $report['averageSales']);
        $this->assertEquals(0, $report['days'][0]['revenue']);
    }

    public function test_dashboard_renders_and_validates_filters_and_keeps_checkbox_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard', ['period' => 'today', 'metrics_submitted' => 1, 'metrics' => ['sales']]))
            ->assertOk()->assertSee('Average Daily Sale')->assertSee('business-statistics-chart')
            ->assertViewHas('selectedMetrics', ['sales']);
        $this->get(route('admin.dashboard', ['metrics_submitted' => 1]))->assertOk()->assertViewHas('selectedMetrics', []);
        foreach ([
            ['period' => 'invalid'],
            ['period' => 'range'],
            ['period' => 'range', 'start_date' => '2026-09-12', 'end_date' => '2026-09-11'],
            ['period' => 'range', 'start_date' => '2026-09-12', 'end_date' => '2026-09-13'],
            ['period' => 'range', 'start_date' => 'bad-date', 'end_date' => '2026-09-12'],
            ['period' => 'range', 'start_date' => '2020-01-01', 'end_date' => '2026-09-12'],
            ['metrics' => ['invalid']],
        ] as $filters) {
            $this->getJson(route('admin.dashboard', $filters))->assertUnprocessable();
        }
    }
}
