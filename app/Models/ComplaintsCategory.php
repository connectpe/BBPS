<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintsCategory extends Model
{
    protected $fillable = ['category_name', 'status', 'updated_by',];
}
