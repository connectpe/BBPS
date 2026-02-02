<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    protected $fillable = ['scheme_name', 'is_active', 'updated_by'];
}
