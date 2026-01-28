<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $table = 'business_categories';
    protected $fillable = ['name', 'slug', 'status'];

    public function businesses()
    {
        return $this->hasMany(BusinessInfo::class);
    }
}
