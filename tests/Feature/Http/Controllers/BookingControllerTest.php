<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\BookingController
 */
final class BookingControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $bookings = Booking::factory()->count(3)->create();

        $response = $this->get(route('bookings.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\BookingController::class,
            'store',
            \App\Http\Requests\BookingStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $check_in_date = Carbon::parse($this->faker->date());
        $check_out_date = Carbon::parse($this->faker->date());
        $total_amount = $this->faker->randomFloat(/** float_attributes **/);
        $payment_status = $this->faker->randomElement(/** enum_attributes **/);
        $is_confirmed = $this->faker->boolean();
        $is_checked_in = $this->faker->boolean();
        $is_checked_out = $this->faker->boolean();
        $no_of_guests = $this->faker->word();
        $no_of_nights = $this->faker->word();
        $booking_id = $this->faker->word();

        $response = $this->post(route('bookings.store'), [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'check_in_date' => $check_in_date->toDateString(),
            'check_out_date' => $check_out_date->toDateString(),
            'total_amount' => $total_amount,
            'payment_status' => $payment_status,
            'is_confirmed' => $is_confirmed,
            'is_checked_in' => $is_checked_in,
            'is_checked_out' => $is_checked_out,
            'no_of_guests' => $no_of_guests,
            'no_of_nights' => $no_of_nights,
            'booking_id' => $booking_id,
        ]);

        $bookings = Booking::query()
            ->where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->where('check_in_date', $check_in_date)
            ->where('check_out_date', $check_out_date)
            ->where('total_amount', $total_amount)
            ->where('payment_status', $payment_status)
            ->where('is_confirmed', $is_confirmed)
            ->where('is_checked_in', $is_checked_in)
            ->where('is_checked_out', $is_checked_out)
            ->where('no_of_guests', $no_of_guests)
            ->where('no_of_nights', $no_of_nights)
            ->where('booking_id', $booking_id)
            ->get();
        $this->assertCount(1, $bookings);
        $booking = $bookings->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->get(route('bookings.show', $booking));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\BookingController::class,
            'update',
            \App\Http\Requests\BookingUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $booking = Booking::factory()->create();
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $check_in_date = Carbon::parse($this->faker->date());
        $check_out_date = Carbon::parse($this->faker->date());
        $total_amount = $this->faker->randomFloat(/** float_attributes **/);
        $payment_status = $this->faker->randomElement(/** enum_attributes **/);
        $is_confirmed = $this->faker->boolean();
        $is_checked_in = $this->faker->boolean();
        $is_checked_out = $this->faker->boolean();
        $no_of_guests = $this->faker->word();
        $no_of_nights = $this->faker->word();
        $booking_id = $this->faker->word();

        $response = $this->put(route('bookings.update', $booking), [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'check_in_date' => $check_in_date->toDateString(),
            'check_out_date' => $check_out_date->toDateString(),
            'total_amount' => $total_amount,
            'payment_status' => $payment_status,
            'is_confirmed' => $is_confirmed,
            'is_checked_in' => $is_checked_in,
            'is_checked_out' => $is_checked_out,
            'no_of_guests' => $no_of_guests,
            'no_of_nights' => $no_of_nights,
            'booking_id' => $booking_id,
        ]);

        $booking->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals($room->id, $booking->room_id);
        $this->assertEquals($check_in_date, $booking->check_in_date);
        $this->assertEquals($check_out_date, $booking->check_out_date);
        $this->assertEquals($total_amount, $booking->total_amount);
        $this->assertEquals($payment_status, $booking->payment_status);
        $this->assertEquals($is_confirmed, $booking->is_confirmed);
        $this->assertEquals($is_checked_in, $booking->is_checked_in);
        $this->assertEquals($is_checked_out, $booking->is_checked_out);
        $this->assertEquals($no_of_guests, $booking->no_of_guests);
        $this->assertEquals($no_of_nights, $booking->no_of_nights);
        $this->assertEquals($booking_id, $booking->booking_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->delete(route('bookings.destroy', $booking));

        $response->assertNoContent();

        $this->assertSoftDeleted($booking);
    }
}
