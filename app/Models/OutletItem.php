<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'price',
        'description',
        'available',
        'tax_rate',
        'image_url',
        'sku',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'category_id' => 'integer',
        'price' => 'float',
        'available' => 'boolean',
        'tax_rate' => 'float',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OutletItemCategory::class, 'category_id');
    }
}
