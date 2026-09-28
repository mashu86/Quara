<?php

namespace Tests\Feature;

use App\Models\SizeMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeMasterChestWaistTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_categories_have_chest_and_waist_from_xxs_to_5xl(): void
    {
        foreach (['korean-ladies-shirt', 'korean-crop-top', 'korean-top', 'ladies-t-shirt', 'ladies-overcoat-long-coat', 'ladies-sweater'] as $slug) {
            $master = SizeMaster::where('slug', $slug)->firstOrFail();
            $this->assertCount(10, $master->rows);
            $this->assertSame('XXS', $master->rows->first()->size_label);
            $this->assertSame('30', $master->rows->first()->chest);
            $this->assertSame('24', $master->rows->first()->waist);
            $this->assertSame('5XL', $master->rows->last()->size_label);
            $this->assertSame('48', $master->rows->last()->chest);
            $this->assertSame('42', $master->rows->last()->waist);
            $this->assertTrue($master->rows->every(fn ($row) => $row->length === null));
        }
    }

    public function test_master_forms_and_chart_use_only_chest_and_waist(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $master = SizeMaster::where('slug', 'ladies-sweater')->firstOrFail();
        $this->get(route('admin.size-guide.index', ['category_id' => $master->id]))
            ->assertOk()->assertSee('Body Reference')
            ->assertDontSee('name="length"', false)->assertDontSee('Length (L)');
        $response = $this->getJson(route('admin.size-masters.chart-json', $master));
        $response->assertOk()->assertJsonPath('rows.0.size_label', 'XXS')
            ->assertJsonPath('rows.0.chest', '30')->assertJsonPath('rows.0.waist', '24');
        $this->assertArrayNotHasKey('length', $response->json('rows.0'));
        $this->post(route('admin.size-masters.row.store', $master), [
            'size_label' => 'Free Size', 'chest' => '34-38', 'waist' => '28-32', 'length' => '99',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('size_master_rows', [
            'size_master_id' => $master->id, 'size_label' => 'Free Size', 'chest' => '34-38',
            'waist' => '28-32', 'length' => null,
        ]);
        $this->get(route('products.size-guide', ['category_id' => $master->id]))
            ->assertOk()->assertSee('Body Measurement Guide')->assertDontSee('Length (L)');
    }
}
