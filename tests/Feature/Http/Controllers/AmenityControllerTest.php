<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\AmenityController
 */
final class AmenityControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $amenities = Amenity::factory()->count(3)->create();

        $response = $this->get(route('amenities.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\AmenityController::class,
            'store',
            \App\Http\Requests\AmenityStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $icon = $this->faker->word();

        $response = $this->post(route('amenities.store'), [
            'name' => $name,
            'icon' => $icon,
        ]);

        $amenities = Amenity::query()
            ->where('name', $name)
            ->where('icon', $icon)
            ->get();
        $this->assertCount(1, $amenities);
        $amenity = $amenities->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $amenity = Amenity::factory()->create();

        $response = $this->get(route('amenities.show', $amenity));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\AmenityController::class,
            'update',
            \App\Http\Requests\AmenityUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $amenity = Amenity::factory()->create();
        $name = $this->faker->name();
        $icon = $this->faker->word();

        $response = $this->put(route('amenities.update', $amenity), [
            'name' => $name,
            'icon' => $icon,
        ]);

        $amenity->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $amenity->name);
        $this->assertEquals($icon, $amenity->icon);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $amenity = Amenity::factory()->create();

        $response = $this->delete(route('amenities.destroy', $amenity));

        $response->assertNoContent();

        $this->assertSoftDeleted($amenity);
    }
}
