<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['service_id', 'provider_name', 'provider_slug', 'updated_by', 'is_active'];
}
