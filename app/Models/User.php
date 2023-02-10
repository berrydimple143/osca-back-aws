<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_number',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'status',
        'username',
        'password',
        'deleted_by',
        'restored_by',
        'deleted_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_deleted' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function messages()
    {
      return $this->hasMany(Message::class);
    }

    public function benefit()
    {
        return $this->hasOne(Benefit::class);
    }
    public function address()
    {
        return $this->hasOne(Address::class);
    }
    public function classification()
    {
        return $this->hasOne(Classification::class);
    }
    public function detail()
    {
        return $this->hasOne(Detail::class);
    }
    public function sickness()
    {
        return $this->hasOne(Sickness::class);
    }
    public function signature()
    {
        return $this->hasOne(Signature::class);
    }
    public function photo()
    {
        return $this->hasOne(Photo::class);
    }
    public function qrcode()
    {
        return $this->hasOne(Qrcode::class);
    }
}
