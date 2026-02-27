<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['service_id', 'provider_name', 'provider_slug', 'updated_by', 'is_active'];

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }


    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
     

    public function orders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }

}


