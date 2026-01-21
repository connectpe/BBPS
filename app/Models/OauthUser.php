<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthUser extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'client_id',
        'client_secret',
        'is_active',
        
    ];
}
