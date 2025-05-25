<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Booking;
use App\Models\CancelationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CancelationRequestController
 */
final class CancelationRequestControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $cancelationRequests = CancelationRequest::factory()->count(3)->create();

        $response = $this->get(route('cancelation-requests.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CancelationRequestController::class,
            'store',
            \App\Http\Requests\CancelationRequestStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $booking = Booking::factory()->create();
        $status = $this->faker->randomElement(/** enum_attributes **/);

        $response = $this->post(route('cancelation-requests.store'), [
            'booking_id' => $booking->id,
            'status' => $status,
        ]);

        $cancelationRequests = CancelationRequest::query()
            ->where('booking_id', $booking->id)
            ->where('status', $status)
            ->get();
        $this->assertCount(1, $cancelationRequests);
        $cancelationRequest = $cancelationRequests->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $cancelationRequest = CancelationRequest::factory()->create();

        $response = $this->get(route('cancelation-requests.show', $cancelationRequest));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CancelationRequestController::class,
            'update',
            \App\Http\Requests\CancelationRequestUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $cancelationRequest = CancelationRequest::factory()->create();
        $booking = Booking::factory()->create();
        $status = $this->faker->randomElement(/** enum_attributes **/);

        $response = $this->put(route('cancelation-requests.update', $cancelationRequest), [
            'booking_id' => $booking->id,
            'status' => $status,
        ]);

        $cancelationRequest->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($booking->id, $cancelationRequest->booking_id);
        $this->assertEquals($status, $cancelationRequest->status);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $cancelationRequest = CancelationRequest::factory()->create();

        $response = $this->delete(route('cancelation-requests.destroy', $cancelationRequest));

        $response->assertNoContent();

        $this->assertSoftDeleted($cancelationRequest);
    }
}
