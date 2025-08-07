<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'description',
        'amount',
        'category',
        'tax_rate',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
