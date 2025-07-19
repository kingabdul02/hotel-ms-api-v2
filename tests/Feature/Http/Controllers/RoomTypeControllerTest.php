<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\RoomTypeController
 */
final class RoomTypeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $roomTypes = RoomType::factory()->count(3)->create();

        $response = $this->get(route('room-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomTypeController::class,
            'store',
            \App\Http\Requests\RoomTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $description = $this->faker->text();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('room-types.store'), [
            'name' => $name,
            'description' => $description,
            'hotel_id' => $hotel->id,
        ]);

        $roomTypes = RoomType::query()
            ->where('name', $name)
            ->where('description', $description)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $roomTypes);
        $roomType = $roomTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $roomType = RoomType::factory()->create();

        $response = $this->get(route('room-types.show', $roomType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomTypeController::class,
            'update',
            \App\Http\Requests\RoomTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $roomType = RoomType::factory()->create();
        $name = $this->faker->name();
        $description = $this->faker->text();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('room-types.update', $roomType), [
            'name' => $name,
            'description' => $description,
            'hotel_id' => $hotel->id,
        ]);

        $roomType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $roomType->name);
        $this->assertEquals($description, $roomType->description);
        $this->assertEquals($hotel->id, $roomType->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $roomType = RoomType::factory()->create();

        $response = $this->delete(route('room-types.destroy', $roomType));

        $response->assertNoContent();

        $this->assertSoftDeleted($roomType);
    }
}
