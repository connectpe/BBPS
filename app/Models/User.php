<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\\Database\\Factories\\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role_id',
        'status',
        'mpin',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // protected $hidden = ['password'];

    // Sanctum provides token management via HasApiTokens

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function business()
    {
        return $this->hasOne(BusinessInfo::class, 'user_id', 'id');
    }

    public function bankDetails()
    {
        return $this->hasOne(UsersBank::class, 'user_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function logs()
    {
        return $this->hasMany(UsersLog::class);
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'Admin';
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    public function nsdlPayments()
    {
        return $this->hasMany(NsdlPayment::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function loadMoneyRequests()
    {
        return $this->hasMany(\App\Models\LoadMoneyRequest::class, 'user_id');
    }
}
