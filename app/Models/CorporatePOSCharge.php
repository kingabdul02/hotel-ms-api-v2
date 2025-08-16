<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporatePOSCharge extends Model
{
    use HasFactory;

    protected $table = 'corporate_pos_charges';

    protected $fillable = [
        'corporate_booking_id',
        'outlet_id',
        'item_id',
        'quantity',
        'unit_price',
        'modifications',
        'server_id',
        'table_number',
        'notes',
        'payment_status',
    ];

    protected $casts = [
        'modifications' => 'array',
    ];

    public function corporateBooking()
    {
        return $this->belongsTo(CorporateBooking::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function item()
    {
        return $this->belongsTo(OutletItem::class, 'item_id');
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_id');
    }
}
