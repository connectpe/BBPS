<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobikwikToken extends Model
{
    protected $table = 'mobikwik_tokens';

    protected $fillable = [
        'token',
        'creation_time',
        'expire_at',
        'response',
    ];
}
