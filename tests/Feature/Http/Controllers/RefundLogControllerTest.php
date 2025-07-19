<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Booking;
use App\Models\RefundLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\RefundLogController
 */
final class RefundLogControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $refundLogs = RefundLog::factory()->count(3)->create();

        $response = $this->get(route('refund-logs.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RefundLogController::class,
            'store',
            \App\Http\Requests\RefundLogStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $booking = Booking::factory()->create();
        $booking_amount = $this->faker->randomFloat(/** float_attributes **/);
        $refund_amount = $this->faker->randomFloat(/** float_attributes **/);
        $fees = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->post(route('refund-logs.store'), [
            'booking_id' => $booking->id,
            'booking_amount' => $booking_amount,
            'refund_amount' => $refund_amount,
            'fees' => $fees,
        ]);

        $refundLogs = RefundLog::query()
            ->where('booking_id', $booking->id)
            ->where('booking_amount', $booking_amount)
            ->where('refund_amount', $refund_amount)
            ->where('fees', $fees)
            ->get();
        $this->assertCount(1, $refundLogs);
        $refundLog = $refundLogs->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $refundLog = RefundLog::factory()->create();

        $response = $this->get(route('refund-logs.show', $refundLog));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RefundLogController::class,
            'update',
            \App\Http\Requests\RefundLogUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $refundLog = RefundLog::factory()->create();
        $booking = Booking::factory()->create();
        $booking_amount = $this->faker->randomFloat(/** float_attributes **/);
        $refund_amount = $this->faker->randomFloat(/** float_attributes **/);
        $fees = $this->faker->randomFloat(/** float_attributes **/);

        $response = $this->put(route('refund-logs.update', $refundLog), [
            'booking_id' => $booking->id,
            'booking_amount' => $booking_amount,
            'refund_amount' => $refund_amount,
            'fees' => $fees,
        ]);

        $refundLog->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($booking->id, $refundLog->booking_id);
        $this->assertEquals($booking_amount, $refundLog->booking_amount);
        $this->assertEquals($refund_amount, $refundLog->refund_amount);
        $this->assertEquals($fees, $refundLog->fees);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $refundLog = RefundLog::factory()->create();

        $response = $this->delete(route('refund-logs.destroy', $refundLog));

        $response->assertNoContent();

        $this->assertModelMissing($refundLog);
    }
}
