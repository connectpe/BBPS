<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BbpsCategory extends Model
{
    protected $fillable = [
        'bbps_category_name',
        'status',
    ];
}
