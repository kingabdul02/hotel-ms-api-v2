<?php

namespace Tests\Feature;

use App\Enums\E_PaymentMethod;
use App\Enums\E_PaymentStatus;
use App\Models\Booking;
use App\Models\PaymentEntry;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_payment_with_cash()
    {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Create a booking with payment entry
        $room = Room::factory()->create(['is_available' => true]);
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'is_confirmed' => false,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        PaymentEntry::factory()->create([
            'booking_id' => $booking->id,
            'payment_amount' => 1000,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => $booking->booking_id,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'booking_id',
                    'is_confirmed',
                    'payment_status',
                ]
            ]);

        // Verify booking is updated
        $booking->refresh();
        $this->assertTrue($booking->is_confirmed);
        $this->assertEquals(E_PaymentStatus::PAID, $booking->payment_status);

        // Verify room is updated
        $room->refresh();
        $this->assertFalse($room->is_available);

        // Verify payment entry is updated
        $paymentEntry = $booking->paymentEntry;
        $this->assertEquals(E_PaymentStatus::SUCCESSFUL, $paymentEntry->payment_status);
        $this->assertEquals(E_PaymentMethod::CASH, $paymentEntry->payment_method);
        $this->assertNotNull($paymentEntry->payment_date);
    }

    public function test_admin_can_complete_payment_with_pos()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $room = Room::factory()->create(['is_available' => true]);
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'is_confirmed' => false,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        PaymentEntry::factory()->create([
            'booking_id' => $booking->id,
            'payment_amount' => 1000,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => $booking->booking_id,
                'payment_method' => 'pos',
            ]);

        $response->assertStatus(200);

        $paymentEntry = $booking->fresh()->paymentEntry;
        $this->assertEquals(E_PaymentMethod::POS, $paymentEntry->payment_method);
    }

    public function test_admin_can_complete_payment_with_transfer()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $room = Room::factory()->create(['is_available' => true]);
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'is_confirmed' => false,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        PaymentEntry::factory()->create([
            'booking_id' => $booking->id,
            'payment_amount' => 1000,
            'payment_status' => E_PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => $booking->booking_id,
                'payment_method' => 'transfer',
            ]);

        $response->assertStatus(200);

        $paymentEntry = $booking->fresh()->paymentEntry;
        $this->assertEquals(E_PaymentMethod::TRANSFER, $paymentEntry->payment_method);
    }

    public function test_invalid_payment_method_returns_validation_error()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $booking = Booking::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => $booking->booking_id,
                'payment_method' => 'invalid_method',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_non_existent_booking_returns_404()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => 'NON_EXISTENT_BOOKING',
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);
    }

    public function test_non_admin_cannot_complete_payment()
    {
        $guest = User::factory()->create();
        $guest->assignRole('Guest');

        $booking = Booking::factory()->create();

        $response = $this->actingAs($guest)
            ->postJson('/api/admin/complete-payment', [
                'booking_id' => $booking->booking_id,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(403); // or whatever your role middleware returns
    }
}
