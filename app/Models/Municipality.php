<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $table = "municipalities";

    protected $fillable = [
        'municipality_code_number',
        'municipality_code_name',
        'municipality_name',
        'district_no',
    ];
}
