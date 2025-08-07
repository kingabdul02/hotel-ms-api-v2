<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSCharge extends Model
{
    use HasFactory;

    protected $table = 'pos_charges';

    protected $fillable = [
        'booking_id',
        'outlet_id',
        'item_id',
        'quantity',
        'unit_price',
        'modifications',
        'server_id',
        'table_number',
        'notes',
    ];

    protected $casts = [
        'modifications' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_id');
    }
}
