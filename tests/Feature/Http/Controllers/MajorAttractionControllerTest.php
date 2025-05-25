<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\MajorAttraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\MajorAttractionController
 */
final class MajorAttractionControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $majorAttractions = MajorAttraction::factory()->count(3)->create();

        $response = $this->get(route('major-attractions.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MajorAttractionController::class,
            'store',
            \App\Http\Requests\MajorAttractionStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $description = $this->faker->text();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('major-attractions.store'), [
            'description' => $description,
            'hotel_id' => $hotel->id,
        ]);

        $majorAttractions = MajorAttraction::query()
            ->where('description', $description)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $majorAttractions);
        $majorAttraction = $majorAttractions->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $majorAttraction = MajorAttraction::factory()->create();

        $response = $this->get(route('major-attractions.show', $majorAttraction));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\MajorAttractionController::class,
            'update',
            \App\Http\Requests\MajorAttractionUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $majorAttraction = MajorAttraction::factory()->create();
        $description = $this->faker->text();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('major-attractions.update', $majorAttraction), [
            'description' => $description,
            'hotel_id' => $hotel->id,
        ]);

        $majorAttraction->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($description, $majorAttraction->description);
        $this->assertEquals($hotel->id, $majorAttraction->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $majorAttraction = MajorAttraction::factory()->create();

        $response = $this->delete(route('major-attractions.destroy', $majorAttraction));

        $response->assertNoContent();

        $this->assertSoftDeleted($majorAttraction);
    }
}
