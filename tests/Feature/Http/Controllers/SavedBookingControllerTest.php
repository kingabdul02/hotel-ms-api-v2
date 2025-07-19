<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Booking;
use App\Models\SavedBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SavedBookingController
 */
final class SavedBookingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $savedBookings = SavedBooking::factory()->count(3)->create();

        $response = $this->get(route('saved-bookings.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SavedBookingController::class,
            'store',
            \App\Http\Requests\SavedBookingStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $booking = Booking::factory()->create();
        $user = User::factory()->create();

        $response = $this->post(route('saved-bookings.store'), [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);

        $savedBookings = SavedBooking::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $savedBookings);
        $savedBooking = $savedBookings->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $savedBooking = SavedBooking::factory()->create();

        $response = $this->get(route('saved-bookings.show', $savedBooking));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SavedBookingController::class,
            'update',
            \App\Http\Requests\SavedBookingUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $savedBooking = SavedBooking::factory()->create();
        $booking = Booking::factory()->create();
        $user = User::factory()->create();

        $response = $this->put(route('saved-bookings.update', $savedBooking), [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);

        $savedBooking->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($booking->id, $savedBooking->booking_id);
        $this->assertEquals($user->id, $savedBooking->user_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $savedBooking = SavedBooking::factory()->create();

        $response = $this->delete(route('saved-bookings.destroy', $savedBooking));

        $response->assertNoContent();

        $this->assertSoftDeleted($savedBooking);
    }
}
