<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'email',
        'phone',
        'address'
    ];

    public function bookings()
    {
        return $this->hasMany(CorporateBooking::class);
    }
}
