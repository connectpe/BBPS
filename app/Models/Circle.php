<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    protected $fillable = ['name', 'code', 'status'];

    public function operators()
    {
        return $this->hasMany(Operator::class);
    }
}
