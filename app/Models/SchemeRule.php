<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeRule extends Model
{
    protected $fillable = ['scheme_id', 'service_id', 'start_value', 'end_value', 'type', 'fee', 'min_fee', 'max_fee', 'is_active', 'updated_by'];
}
