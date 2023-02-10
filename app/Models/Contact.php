<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = "contact_details";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'phone',
        'mobile',
        'contact_person',
        'contact_person_number',
        'contact_person_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
