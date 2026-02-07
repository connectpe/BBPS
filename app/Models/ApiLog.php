<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'endpoint',
        'request_body',
        'response_body',
        'status_code',
        'ip_address',
        'user_agent',
        'execution_time',
        'location_details'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
