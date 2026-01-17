<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = [
        'circle_id', 'name', 'code', 'service_type', 'status'
    ];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

