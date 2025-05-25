<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\InventoryMovementController
 */
final class InventoryMovementControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $inventoryMovements = InventoryMovement::factory()->count(3)->create();

        $response = $this->get(route('inventory-movements.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InventoryMovementController::class,
            'store',
            \App\Http\Requests\InventoryMovementStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $item = Item::factory()->create();
        $transaction_type = $this->faker->randomElement(/** enum_attributes **/);
        $quantity = $this->faker->word();
        $transaction_date = Carbon::parse($this->faker->dateTime());
        $remarks = $this->faker->text();

        $response = $this->post(route('inventory-movements.store'), [
            'item_id' => $item->id,
            'transaction_type' => $transaction_type,
            'quantity' => $quantity,
            'transaction_date' => $transaction_date->toDateTimeString(),
            'remarks' => $remarks,
        ]);

        $inventoryMovements = InventoryMovement::query()
            ->where('item_id', $item->id)
            ->where('transaction_type', $transaction_type)
            ->where('quantity', $quantity)
            ->where('transaction_date', $transaction_date)
            ->where('remarks', $remarks)
            ->get();
        $this->assertCount(1, $inventoryMovements);
        $inventoryMovement = $inventoryMovements->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $inventoryMovement = InventoryMovement::factory()->create();

        $response = $this->get(route('inventory-movements.show', $inventoryMovement));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InventoryMovementController::class,
            'update',
            \App\Http\Requests\InventoryMovementUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $inventoryMovement = InventoryMovement::factory()->create();
        $item = Item::factory()->create();
        $transaction_type = $this->faker->randomElement(/** enum_attributes **/);
        $quantity = $this->faker->word();
        $transaction_date = Carbon::parse($this->faker->dateTime());
        $remarks = $this->faker->text();

        $response = $this->put(route('inventory-movements.update', $inventoryMovement), [
            'item_id' => $item->id,
            'transaction_type' => $transaction_type,
            'quantity' => $quantity,
            'transaction_date' => $transaction_date->toDateTimeString(),
            'remarks' => $remarks,
        ]);

        $inventoryMovement->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($item->id, $inventoryMovement->item_id);
        $this->assertEquals($transaction_type, $inventoryMovement->transaction_type);
        $this->assertEquals($quantity, $inventoryMovement->quantity);
        $this->assertEquals($transaction_date, $inventoryMovement->transaction_date);
        $this->assertEquals($remarks, $inventoryMovement->remarks);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $inventoryMovement = InventoryMovement::factory()->create();

        $response = $this->delete(route('inventory-movements.destroy', $inventoryMovement));

        $response->assertNoContent();

        $this->assertSoftDeleted($inventoryMovement);
    }
}
