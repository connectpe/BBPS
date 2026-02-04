<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignedToSupport extends Model
{
    protected $fillable = [
        'user_id',
        'updated_by',
        
    ];

    public $timestamps = true;
}
