<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\InventoryController
 */
final class InventoryControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $inventories = Inventory::factory()->count(3)->create();

        $response = $this->get(route('inventories.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InventoryController::class,
            'store',
            \App\Http\Requests\InventoryStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $last_updated = Carbon::parse($this->faker->dateTime());

        $response = $this->post(route('inventories.store'), [
            'item_id' => $item->id,
            'quantity' => $quantity,
            'last_updated' => $last_updated->toDateTimeString(),
        ]);

        $inventories = Inventory::query()
            ->where('item_id', $item->id)
            ->where('quantity', $quantity)
            ->where('last_updated', $last_updated)
            ->get();
        $this->assertCount(1, $inventories);
        $inventory = $inventories->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->get(route('inventories.show', $inventory));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InventoryController::class,
            'update',
            \App\Http\Requests\InventoryUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $inventory = Inventory::factory()->create();
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $last_updated = Carbon::parse($this->faker->dateTime());

        $response = $this->put(route('inventories.update', $inventory), [
            'item_id' => $item->id,
            'quantity' => $quantity,
            'last_updated' => $last_updated->toDateTimeString(),
        ]);

        $inventory->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($item->id, $inventory->item_id);
        $this->assertEquals($quantity, $inventory->quantity);
        $this->assertEquals($last_updated, $inventory->last_updated);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->delete(route('inventories.destroy', $inventory));

        $response->assertNoContent();

        $this->assertSoftDeleted($inventory);
    }
}
