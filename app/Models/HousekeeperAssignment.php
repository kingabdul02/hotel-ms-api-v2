<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeeperAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'housekeeper_id',
        'room_id',
        'assignment_date',
        'shift',
        'status',
        'notes',
    ];

    public function housekeeper()
    {
        return $this->belongsTo(User::class, 'housekeeper_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
