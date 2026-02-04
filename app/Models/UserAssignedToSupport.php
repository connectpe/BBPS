<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignedToSupport extends Model
{
    protected $fillable = [
        'user_id',
        'updated_by',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public $timestamps = true;
}
