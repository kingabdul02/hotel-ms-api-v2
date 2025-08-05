<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBookingHall extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_booking_id',
        'hall_id',
        'hall_name',
        'hall_price',
        'start_date',
        'end_date',
        'amount',
    ];

    protected $casts = [
        'hall_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function corporateBooking()
    {
        return $this->belongsTo(CorporateBooking::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
