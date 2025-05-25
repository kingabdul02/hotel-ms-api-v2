<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PaymentEntryController
 */
final class PaymentEntryControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $paymentEntries = PaymentEntry::factory()->count(3)->create();

        $response = $this->get(route('payment-entries.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PaymentEntryController::class,
            'store',
            \App\Http\Requests\PaymentEntryStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $booking = Booking::factory()->create();
        $payment_amount = $this->faker->randomFloat(/** float_attributes **/);
        $payment_method = $this->faker->randomElement(/** enum_attributes **/);
        $transaction_id = $this->faker->word();
        $payment_status = $this->faker->randomElement(/** enum_attributes **/);
        $payment_date = Carbon::parse($this->faker->dateTime());

        $response = $this->post(route('payment-entries.store'), [
            'booking_id' => $booking->id,
            'payment_amount' => $payment_amount,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'payment_status' => $payment_status,
            'payment_date' => $payment_date->toDateTimeString(),
        ]);

        $paymentEntries = PaymentEntry::query()
            ->where('booking_id', $booking->id)
            ->where('payment_amount', $payment_amount)
            ->where('payment_method', $payment_method)
            ->where('transaction_id', $transaction_id)
            ->where('payment_status', $payment_status)
            ->where('payment_date', $payment_date)
            ->get();
        $this->assertCount(1, $paymentEntries);
        $paymentEntry = $paymentEntries->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $paymentEntry = PaymentEntry::factory()->create();

        $response = $this->get(route('payment-entries.show', $paymentEntry));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PaymentEntryController::class,
            'update',
            \App\Http\Requests\PaymentEntryUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $paymentEntry = PaymentEntry::factory()->create();
        $booking = Booking::factory()->create();
        $payment_amount = $this->faker->randomFloat(/** float_attributes **/);
        $payment_method = $this->faker->randomElement(/** enum_attributes **/);
        $transaction_id = $this->faker->word();
        $payment_status = $this->faker->randomElement(/** enum_attributes **/);
        $payment_date = Carbon::parse($this->faker->dateTime());

        $response = $this->put(route('payment-entries.update', $paymentEntry), [
            'booking_id' => $booking->id,
            'payment_amount' => $payment_amount,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'payment_status' => $payment_status,
            'payment_date' => $payment_date->toDateTimeString(),
        ]);

        $paymentEntry->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($booking->id, $paymentEntry->booking_id);
        $this->assertEquals($payment_amount, $paymentEntry->payment_amount);
        $this->assertEquals($payment_method, $paymentEntry->payment_method);
        $this->assertEquals($transaction_id, $paymentEntry->transaction_id);
        $this->assertEquals($payment_status, $paymentEntry->payment_status);
        $this->assertEquals($payment_date->timestamp, $paymentEntry->payment_date);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $paymentEntry = PaymentEntry::factory()->create();

        $response = $this->delete(route('payment-entries.destroy', $paymentEntry));

        $response->assertNoContent();

        $this->assertSoftDeleted($paymentEntry);
    }
}
