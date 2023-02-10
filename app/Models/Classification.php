<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    use HasFactory;

    protected $table = "user_classification";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'classification',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
