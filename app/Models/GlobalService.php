<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalService extends Model
{
    protected $table = 'global_services';

    protected $fillable = [
        'user_id',
        'service_name',
        'slug',
        'is_active',
    ];
}
