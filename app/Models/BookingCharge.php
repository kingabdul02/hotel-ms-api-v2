<?php

namespace App\Models;

use App\Enums\E_PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
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

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
