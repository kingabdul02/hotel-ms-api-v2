<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'operating_hours',
    ];

    protected $casts = [
        'operating_hours' => 'array',
    ];

    public function categories()
    {
        return $this->hasMany(OutletItemCategory::class, 'outlet_id');
    }

    public function items()
    {
        return $this->hasMany(OutletItem::class, 'outlet_id');
    }
}
