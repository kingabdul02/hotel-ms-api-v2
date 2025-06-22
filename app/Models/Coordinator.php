<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{
    use HasFactory;
    protected $fillable = ['company_id', 'full_name', 'email', 'phone', 'nin', 'id_card_file'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
