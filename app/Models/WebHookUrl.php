<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebHookUrl extends Model
{
    protected $fillable = ['user_id', 'url', 'updated_by', 'service_id', 'service_slug'];

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id')->where('is_active', '1');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
