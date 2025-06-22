<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBookingGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_booking_id',
        'room_id',
        'full_name',
        'gender',
        'email',
        'phone',
        'is_checked_in',
        'checked_in_at',
        'is_checked_out',
        'checked_out_at'
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'is_checked_in' => 'boolean',
        'is_checked_out' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(CorporateBooking::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
