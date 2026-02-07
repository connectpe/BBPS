<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultProvider extends Model
{
    protected $fillable = ['service_id', 'provider_id', 'provider_slug', 'updated_by'];

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i A',
        'updated_at' => 'datetime:d-m-Y h:i A',
    ];
}
