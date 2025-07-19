<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PurchaseOrderController
 */
final class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $purchaseOrders = PurchaseOrder::factory()->count(3)->create();

        $response = $this->get(route('purchase-orders.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PurchaseOrderController::class,
            'store',
            \App\Http\Requests\PurchaseOrderStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $supplier = Supplier::factory()->create();
        $order_date = Carbon::parse($this->faker->date());
        $total_amount = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'order_date' => $order_date->toDateString(),
            'total_amount' => $total_amount,
        ]);

        $purchaseOrders = PurchaseOrder::query()
            ->where('supplier_id', $supplier->id)
            ->where('order_date', $order_date)
            ->where('total_amount', $total_amount)
            ->get();
        $this->assertCount(1, $purchaseOrders);
        $purchaseOrder = $purchaseOrders->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = $this->get(route('purchase-orders.show', $purchaseOrder));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PurchaseOrderController::class,
            'update',
            \App\Http\Requests\PurchaseOrderUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $supplier = Supplier::factory()->create();
        $order_date = Carbon::parse($this->faker->date());
        $total_amount = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->put(route('purchase-orders.update', $purchaseOrder), [
            'supplier_id' => $supplier->id,
            'order_date' => $order_date->toDateString(),
            'total_amount' => $total_amount,
        ]);

        $purchaseOrder->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($supplier->id, $purchaseOrder->supplier_id);
        $this->assertEquals($order_date, $purchaseOrder->order_date);
        $this->assertEquals($total_amount, $purchaseOrder->total_amount);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = $this->delete(route('purchase-orders.destroy', $purchaseOrder));

        $response->assertNoContent();

        $this->assertSoftDeleted($purchaseOrder);
    }
}
