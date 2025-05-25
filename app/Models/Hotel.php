<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'location',
        'phone',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class);
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function policyTypes(): HasMany
    {
        return $this->hasMany(PolicyType::class);
    }

    public function hotelPolicies(): HasMany
    {
        return $this->hasMany(HotelPolicy::class);
    }

    public function hotelImages(): HasMany
    {
        return $this->hasMany(HotelImage::class);
    }

    public function majorAttractions(): HasMany
    {
        return $this->hasMany(MajorAttraction::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
