<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelPolicy;
use App\Models\PolicyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\HotelPolicyController
 */
final class HotelPolicyControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $hotelPolicies = HotelPolicy::factory()->count(3)->create();

        $response = $this->get(route('hotel-policies.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelPolicyController::class,
            'store',
            \App\Http\Requests\HotelPolicyStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $policy = $this->faker->word();
        $policy_type = PolicyType::factory()->create();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('hotel-policies.store'), [
            'policy' => $policy,
            'policy_type_id' => $policy_type->id,
            'hotel_id' => $hotel->id,
        ]);

        $hotelPolicies = HotelPolicy::query()
            ->where('policy', $policy)
            ->where('policy_type_id', $policy_type->id)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $hotelPolicies);
        $hotelPolicy = $hotelPolicies->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $hotelPolicy = HotelPolicy::factory()->create();

        $response = $this->get(route('hotel-policies.show', $hotelPolicy));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelPolicyController::class,
            'update',
            \App\Http\Requests\HotelPolicyUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $hotelPolicy = HotelPolicy::factory()->create();
        $policy = $this->faker->word();
        $policy_type = PolicyType::factory()->create();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('hotel-policies.update', $hotelPolicy), [
            'policy' => $policy,
            'policy_type_id' => $policy_type->id,
            'hotel_id' => $hotel->id,
        ]);

        $hotelPolicy->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($policy, $hotelPolicy->policy);
        $this->assertEquals($policy_type->id, $hotelPolicy->policy_type_id);
        $this->assertEquals($hotel->id, $hotelPolicy->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $hotelPolicy = HotelPolicy::factory()->create();

        $response = $this->delete(route('hotel-policies.destroy', $hotelPolicy));

        $response->assertNoContent();

        $this->assertSoftDeleted($hotelPolicy);
    }
}
