<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qrcode extends Model
{
    use HasFactory;
    protected $table = "user_qrcodes";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'qrcode',
        'qrcode_back',
        'counter',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
