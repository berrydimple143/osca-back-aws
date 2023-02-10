<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sickness extends Model
{
    use HasFactory;

    protected $table = "user_illness";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sickness',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
