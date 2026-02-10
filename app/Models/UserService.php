<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    use HasFactory;

    protected $table = 'user_services';

    protected $fillable = [
        'user_id',
        'service_id',
        'default_slug',
        'status',
        'transaction_amount',
        'is_api_enable',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }
}
