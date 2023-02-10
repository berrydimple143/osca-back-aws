<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = "user_address";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'region_name',
        'province',
        'province_name',
        'municipality',
        'municipality_name',
        'city',
        'city_name',
        'barangay',
        'barangay_name',
        'address',
        'birth_place',
        'district_no',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
