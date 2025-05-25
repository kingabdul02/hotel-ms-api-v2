<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PurchaseOrderItemController
 */
final class PurchaseOrderItemControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $purchaseOrderItems = PurchaseOrderItem::factory()->count(3)->create();

        $response = $this->get(route('purchase-order-items.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PurchaseOrderItemController::class,
            'store',
            \App\Http\Requests\PurchaseOrderItemStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $purchase_order = PurchaseOrder::factory()->create();
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $unit_price = $this->faker->randomFloat(/** float_attributes **/);
        $total_price = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->post(route('purchase-order-items.store'), [
            'purchase_order_id' => $purchase_order->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_price' => $total_price,
        ]);

        $purchaseOrderItems = PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchase_order->id)
            ->where('item_id', $item->id)
            ->where('quantity', $quantity)
            ->where('unit_price', $unit_price)
            ->where('total_price', $total_price)
            ->get();
        $this->assertCount(1, $purchaseOrderItems);
        $purchaseOrderItem = $purchaseOrderItems->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $purchaseOrderItem = PurchaseOrderItem::factory()->create();

        $response = $this->get(route('purchase-order-items.show', $purchaseOrderItem));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PurchaseOrderItemController::class,
            'update',
            \App\Http\Requests\PurchaseOrderItemUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $purchaseOrderItem = PurchaseOrderItem::factory()->create();
        $purchase_order = PurchaseOrder::factory()->create();
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $unit_price = $this->faker->randomFloat(/** float_attributes **/);
        $total_price = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->put(route('purchase-order-items.update', $purchaseOrderItem), [
            'purchase_order_id' => $purchase_order->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_price' => $total_price,
        ]);

        $purchaseOrderItem->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($purchase_order->id, $purchaseOrderItem->purchase_order_id);
        $this->assertEquals($item->id, $purchaseOrderItem->item_id);
        $this->assertEquals($quantity, $purchaseOrderItem->quantity);
        $this->assertEquals($unit_price, $purchaseOrderItem->unit_price);
        $this->assertEquals($total_price, $purchaseOrderItem->total_price);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $purchaseOrderItem = PurchaseOrderItem::factory()->create();

        $response = $this->delete(route('purchase-order-items.destroy', $purchaseOrderItem));

        $response->assertNoContent();

        $this->assertSoftDeleted($purchaseOrderItem);
    }
}
