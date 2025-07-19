<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Department;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\DepartmentController
 */
final class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $departments = Department::factory()->count(3)->create();

        $response = $this->get(route('departments.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\DepartmentController::class,
            'store',
            \App\Http\Requests\DepartmentStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('departments.store'), [
            'name' => $name,
            'hotel_id' => $hotel->id,
        ]);

        $departments = Department::query()
            ->where('name', $name)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $departments);
        $department = $departments->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $department = Department::factory()->create();

        $response = $this->get(route('departments.show', $department));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\DepartmentController::class,
            'update',
            \App\Http\Requests\DepartmentUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $department = Department::factory()->create();
        $name = $this->faker->name();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('departments.update', $department), [
            'name' => $name,
            'hotel_id' => $hotel->id,
        ]);

        $department->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $department->name);
        $this->assertEquals($hotel->id, $department->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $department = Department::factory()->create();

        $response = $this->delete(route('departments.destroy', $department));

        $response->assertNoContent();

        $this->assertSoftDeleted($department);
    }
}
