<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'special_requests',
        'total_amount',
        'payment_status',
        'is_confirmed',
        'is_checked_in',
        'is_checked_out',
        'no_of_guests',
        'no_of_nights',
        'booking_id',
        'guest_name',
        'is_online_booking',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'room_id' => 'integer',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'float',
        'is_confirmed' => 'boolean',
        'is_checked_in' => 'boolean',
        'is_checked_out' => 'boolean',
    ];

    public function paymentEntry(): HasOne
    {
        return $this->hasOne(PaymentEntry::class);
    }

    public function cancelationRequest(): HasOne
    {
        return $this->hasOne(CancelationRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
