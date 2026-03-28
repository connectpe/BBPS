<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenances';

    protected $fillable = [
        'status',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
