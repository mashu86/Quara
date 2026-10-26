<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipment;
use App\Models\Product;
use App\Models\UnmappedCourierArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkArticleConnectTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, string $name, string $pin, float $weightKg, int $quantity): Order
    {
        $order = Order::create([
            'order_number' => $number, 'customer_name' => $name, 'customer_phone' => '9876543210',
            'house_building' => 'House 1', 'street' => 'MG Road', 'area' => 'Kakkanad', 'city' => 'Kochi',
            'district' => 'Ernakulam', 'state' => 'Kerala', 'pin_code' => $pin,
            'subtotal' => 1000, 'discount' => 0, 'shipping' => 0, 'grand_total' => 1000,
            'payment_method' => 'online', 'payment_status' => 'paid', 'order_status' => 'confirmed',
        ]);
        $category = Category::firstOrCreate(['slug' => 'article-tests'], ['name' => 'Article Tests', 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Dress '.$number, 'price' => 500, 'status' => 'active', 'weight_kg' => $weightKg]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'size' => 'M', 'quantity' => $quantity, 'unit_price' => 500, 'final_unit_price' => 500, 'subtotal' => 500 * $quantity, 'item_status' => 'active']);

        return $order;
    }

    private function importSession(array $bucket): array
    {
        return [
            'article_bucket' => $bucket,
            'article_import' => [
                'headers' => ['Receiver-Name', 'Receiver-Address', 'Destination-Pin', 'Article', 'Tariff'],
                'rows' => [
                    ['Anu K', 'Kakkanad Kochi', '682030', 'EA111IN', '45'],
                    ['Someone Else', 'Thrissur', '680001', 'EA222IN', '50'],
                    ['Rahul M', 'Thrissur', '680001', 'EA333IN', '40'],
                ],
            ],
        ];
    }

    private const MAP = ['map' => ['receiver_name' => 0, 'receiver_address' => 1, 'pin_code' => 2, 'article_number' => 3, 'courier_charge' => 4]];

    public function test_same_name_receivers_are_connected_automatically(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $anu = $this->order('Q-1', 'Anu K', '682030', 0.25, 2);
        $rahul = $this->order('Q-2', 'Rahul Menon', '680001', 0.5, 1);

        $response = $this->withSession($this->importSession([$anu->id, $rahul->id]))
            ->post(route('admin.bulk-article.match'), self::MAP)->assertOk()->assertViewIs('admin.bulk_article.connect');

        $this->assertSame([0 => $anu->id], $response->viewData('pairs'));
        $this->assertEquals(500, $response->viewData('orders')->firstWhere('id', $anu->id)->website_weight);
    }

    public function test_only_connected_pairs_are_saved_with_website_weight_and_tariff(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $anu = $this->order('Q-1', 'Anu K', '682030', 0.25, 2);
        $rahul = $this->order('Q-2', 'Rahul Menon', '680001', 0.5, 1);
        $unmapped = $this->order('Q-3', 'Nobody', '600001', 1, 1);

        $this->withSession($this->importSession([$anu->id, $rahul->id, $unmapped->id]))
            ->post(route('admin.bulk-article.match'), self::MAP)->assertOk();
        // Manual connection: "Rahul M" in the file is order holder "Rahul Menon".
        $this->post(route('admin.bulk-article.save'), ['pairs' => [0 => $anu->id, 2 => $rahul->id]])
            ->assertRedirect(route('admin.bulk-article.index'));

        $this->assertSame(2, OrderShipment::count());
        $anuShipment = OrderShipment::where('order_id', $anu->id)->first();
        $this->assertSame('EA111IN', $anuShipment->article_number);
        $this->assertEquals(500, $anuShipment->weight);
        $this->assertEquals(45, $anuShipment->courier_charge);
        $this->assertSame('EA333IN', OrderShipment::where('order_id', $rahul->id)->value('article_number'));
        $this->assertFalse(OrderShipment::where('order_id', $unmapped->id)->exists());
        $this->assertSame([$unmapped->id], session('article_bucket'));
        // The unconnected Excel row is kept, not lost.
        $kept = UnmappedCourierArticle::sole();
        $this->assertSame('EA222IN', $kept->article_number);
        $this->assertSame(UnmappedCourierArticle::NOT_MAPPED, $kept->status);
    }

    public function test_order_search_lists_only_orders_without_tracking(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $open = $this->order('QW-1', 'Bincy Thomas', '689104', 0.3, 3);
        $done = $this->order('QW-2', 'Bincy Mathew', '689104', 0.3, 1);
        OrderShipment::create(['order_id' => $done->id, 'article_number' => 'EA1IN', 'receiver_name' => 'Bincy', 'receiver_address' => 'X']);

        $orders = $this->getJson(route('admin.bulk-article.unmapped.search-orders', ['q' => 'bincy']))->assertOk()->json('orders');
        $this->assertSame(['QW-1'], array_column($orders, 'order_number'));
        $this->assertEquals(900, $orders[0]['weight']);
        $this->assertSame(['QW-1'], array_column($this->getJson(route('admin.bulk-article.unmapped.search-orders', ['q' => 'QW-1']))->json('orders'), 'order_number'));
    }

    public function test_not_mapped_row_can_be_connected_later_by_order_id(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $order = $this->order('QW-20261021-00009', 'Someone', '680001', 0.3, 1);
        $article = UnmappedCourierArticle::create(['article_number' => 'EA222IN', 'tracking_number' => 'EA222IN', 'receiver_name' => 'Someone Else', 'receiver_address' => 'Thrissur', 'courier_charge' => 50]);

        // A row whose article number was already generated elsewhere is not pending.
        $other = $this->order('QW-OTHER', 'Other', '600001', 0.1, 1);
        OrderShipment::create(['order_id' => $other->id, 'article_number' => 'EA999IN', 'tracking_number' => 'EA999IN', 'receiver_name' => 'Other', 'receiver_address' => 'Chennai']);
        UnmappedCourierArticle::create(['article_number' => 'EA999IN', 'receiver_name' => 'Already Done', 'receiver_address' => 'Chennai']);

        $this->get(route('admin.bulk-article.unmapped'))->assertOk()->assertSee('EA222IN')->assertDontSee('Already Done');
        $this->put(route('admin.bulk-article.unmapped.connect', $article), ['order_number' => 'QW-NOPE'])->assertSessionHasErrors('order_number');
        $this->put(route('admin.bulk-article.unmapped.connect', $article), ['order_number' => 'QW-20261021-00009'])
            ->assertRedirect(route('admin.bulk-article.unmapped'));

        $shipment = OrderShipment::where('order_id', $order->id)->sole();
        $this->assertSame('EA222IN', $shipment->article_number);
        $this->assertEquals(300, $shipment->weight);
        $this->assertEquals(50, $shipment->courier_charge);
        $this->assertSame(UnmappedCourierArticle::CONNECTED, $article->fresh()->status);
        $this->assertSame($shipment->id, $article->fresh()->order_shipment_id);
    }
}
