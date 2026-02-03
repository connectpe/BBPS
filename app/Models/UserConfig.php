<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConfig extends Model
{
    protected $fillable = ['user_id', 'scheme_id', 'updated_by', 'is_active'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}

}

