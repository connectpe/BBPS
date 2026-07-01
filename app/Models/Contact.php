<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'type',
        'account_type',
        'account_number',
        'account_ifsc',
        'bank_name',
        'vpa_address',
        'card_number',
        'reference_id',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'contact_id', 'contact_id');
    }
}