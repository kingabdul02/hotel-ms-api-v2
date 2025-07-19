<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'coordinator_id',
        'check_in_date',
        'check_out_date',
        'meal_plan_id',
        'total_amount',
        'status',
        'reservation_code',
        'expected_guests'
    ];

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'expected_guests' => 'integer'
    ];

    public function guests()
    {
        return $this->hasMany(CorporateBookingGuest::class);
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
}
