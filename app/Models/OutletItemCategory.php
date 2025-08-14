<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutletItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OutletItem::class, 'category_id');
    }
}
