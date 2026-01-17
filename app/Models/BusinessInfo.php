<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInfo extends Model
{
    protected $fillable = [
        'user_id', 'business_category_id', 'business_name',
        'address', 'city', 'state', 'pincode'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }
}
