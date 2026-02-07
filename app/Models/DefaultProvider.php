<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultProvider extends Model
{
    protected $fillable = ['service_id', 'provider_id', 'provider_slug', 'updated_by'];
}
