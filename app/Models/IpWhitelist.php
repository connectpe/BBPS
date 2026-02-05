<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $fillable = ['user_id', 'ip_address','is_active','updated_by'];
}
