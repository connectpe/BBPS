<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersLog extends Model
{
    protected $table = 'userslogs';
    protected $fillable = ['user_id', 'action', 'ip_address', 'user_agent', 'time'];

    public function user() {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
