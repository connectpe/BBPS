<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintsCategory extends Model
{
    protected $table = 'complaints_categories';

    protected $fillable = [
        'category_name',
        'status',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
