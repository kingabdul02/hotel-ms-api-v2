<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\DepartmentRequestController
 */
final class DepartmentRequestControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $departmentRequests = DepartmentRequest::factory()->count(3)->create();

        $response = $this->get(route('department-requests.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\DepartmentRequestController::class,
            'store',
            \App\Http\Requests\DepartmentRequestStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $department = Department::factory()->create();
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $request_date = Carbon::parse($this->faker->date());
        $status = $this->faker->randomElement(/** enum_attributes **/);

        $response = $this->post(route('department-requests.store'), [
            'department_id' => $department->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'request_date' => $request_date->toDateString(),
            'status' => $status,
        ]);

        $departmentRequests = DepartmentRequest::query()
            ->where('department_id', $department->id)
            ->where('item_id', $item->id)
            ->where('quantity', $quantity)
            ->where('request_date', $request_date)
            ->where('status', $status)
            ->get();
        $this->assertCount(1, $departmentRequests);
        $departmentRequest = $departmentRequests->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $departmentRequest = DepartmentRequest::factory()->create();

        $response = $this->get(route('department-requests.show', $departmentRequest));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\DepartmentRequestController::class,
            'update',
            \App\Http\Requests\DepartmentRequestUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $departmentRequest = DepartmentRequest::factory()->create();
        $department = Department::factory()->create();
        $item = Item::factory()->create();
        $quantity = $this->faker->word();
        $request_date = Carbon::parse($this->faker->date());
        $status = $this->faker->randomElement(/** enum_attributes **/);

        $response = $this->put(route('department-requests.update', $departmentRequest), [
            'department_id' => $department->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'request_date' => $request_date->toDateString(),
            'status' => $status,
        ]);

        $departmentRequest->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($department->id, $departmentRequest->department_id);
        $this->assertEquals($item->id, $departmentRequest->item_id);
        $this->assertEquals($quantity, $departmentRequest->quantity);
        $this->assertEquals($request_date, $departmentRequest->request_date);
        $this->assertEquals($status, $departmentRequest->status);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $departmentRequest = DepartmentRequest::factory()->create();

        $response = $this->delete(route('department-requests.destroy', $departmentRequest));

        $response->assertNoContent();

        $this->assertSoftDeleted($departmentRequest);
    }
}
