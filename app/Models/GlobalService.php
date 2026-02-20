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

    /**
     * Relationship: A service has many user rootings
     */
    public function userRoot()
    {
        return $this->hasMany(UserRooting::class, 'service_id');
    }

    public function providers()
    {
        return $this->hasMany(Provider::class, 'service_id');
    }
}
