<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BbpsCategoryOperator extends Model
{
    protected $fillable = [
        'bbps_category_id',
        'operator_id',
        'biller_name',
        'biller_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(BbpsCategory::class, 'bbps_category_id');
    }
}
