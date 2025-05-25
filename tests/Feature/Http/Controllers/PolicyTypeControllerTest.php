<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\PolicyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PolicyTypeController
 */
final class PolicyTypeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $policyTypes = PolicyType::factory()->count(3)->create();

        $response = $this->get(route('policy-types.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PolicyTypeController::class,
            'store',
            \App\Http\Requests\PolicyTypeStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('policy-types.store'), [
            'name' => $name,
            'hotel_id' => $hotel->id,
        ]);

        $policyTypes = PolicyType::query()
            ->where('name', $name)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $policyTypes);
        $policyType = $policyTypes->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $policyType = PolicyType::factory()->create();

        $response = $this->get(route('policy-types.show', $policyType));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PolicyTypeController::class,
            'update',
            \App\Http\Requests\PolicyTypeUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $policyType = PolicyType::factory()->create();
        $name = $this->faker->name();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('policy-types.update', $policyType), [
            'name' => $name,
            'hotel_id' => $hotel->id,
        ]);

        $policyType->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $policyType->name);
        $this->assertEquals($hotel->id, $policyType->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $policyType = PolicyType::factory()->create();

        $response = $this->delete(route('policy-types.destroy', $policyType));

        $response->assertNoContent();

        $this->assertSoftDeleted($policyType);
    }
}
