<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\HotelController
 */
final class HotelControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $hotels = Hotel::factory()->count(3)->create();

        $response = $this->get(route('hotels.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelController::class,
            'store',
            \App\Http\Requests\HotelStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $location = $this->faker->word();

        $response = $this->post(route('hotels.store'), [
            'name' => $name,
            'location' => $location,
        ]);

        $hotels = Hotel::query()
            ->where('name', $name)
            ->where('location', $location)
            ->get();
        $this->assertCount(1, $hotels);
        $hotel = $hotels->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->get(route('hotels.show', $hotel));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelController::class,
            'update',
            \App\Http\Requests\HotelUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $hotel = Hotel::factory()->create();
        $name = $this->faker->name();
        $location = $this->faker->word();

        $response = $this->put(route('hotels.update', $hotel), [
            'name' => $name,
            'location' => $location,
        ]);

        $hotel->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $hotel->name);
        $this->assertEquals($location, $hotel->location);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->delete(route('hotels.destroy', $hotel));

        $response->assertNoContent();

        $this->assertSoftDeleted($hotel);
    }
}
