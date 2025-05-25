<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Facility;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FacilityController
 */
final class FacilityControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $facilities = Facility::factory()->count(3)->create();

        $response = $this->get(route('facilities.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\FacilityController::class,
            'store',
            \App\Http\Requests\FacilityStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $icon = $this->faker->word();
        $room = Room::factory()->create();

        $response = $this->post(route('facilities.store'), [
            'name' => $name,
            'icon' => $icon,
            'room_id' => $room->id,
        ]);

        $facilities = Facility::query()
            ->where('name', $name)
            ->where('icon', $icon)
            ->where('room_id', $room->id)
            ->get();
        $this->assertCount(1, $facilities);
        $facility = $facilities->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $facility = Facility::factory()->create();

        $response = $this->get(route('facilities.show', $facility));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\FacilityController::class,
            'update',
            \App\Http\Requests\FacilityUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $facility = Facility::factory()->create();
        $name = $this->faker->name();
        $icon = $this->faker->word();
        $room = Room::factory()->create();

        $response = $this->put(route('facilities.update', $facility), [
            'name' => $name,
            'icon' => $icon,
            'room_id' => $room->id,
        ]);

        $facility->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $facility->name);
        $this->assertEquals($icon, $facility->icon);
        $this->assertEquals($room->id, $facility->room_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $facility = Facility::factory()->create();

        $response = $this->delete(route('facilities.destroy', $facility));

        $response->assertNoContent();

        $this->assertSoftDeleted($facility);
    }
}
