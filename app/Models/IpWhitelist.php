<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'is_deleted', 'service_id', 'is_active', 'updated_by'];

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }

    protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
];
}
