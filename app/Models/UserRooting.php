<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRooting extends Model
{
    use HasFactory;

    protected $table = 'users_rootings';

    protected $fillable = [
        'user_id',
        'service_id',
        'service_unique_id',
        'provider_slug',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'service_id' => 'integer',
    ];

    /**
     * Relation: this rooting belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation: this rooting belongs to a service (global_services)
     */
    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }
}
