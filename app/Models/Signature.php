<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;
    
    protected $table = "user_signatures";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'filename',
        'counter',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
