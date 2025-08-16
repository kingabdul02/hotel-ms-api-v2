<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBookingCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_booking_id',
        'description',
        'amount',
        'quantity',
        'category',
        'tax_rate',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'tax_rate' => 'decimal:2',
    ];

    public function corporateBooking()
    {
        return $this->belongsTo(CorporateBooking::class);
    }
}
