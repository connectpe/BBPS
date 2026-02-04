<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scheme extends Model
{
    protected $fillable = ['scheme_name', 'is_active', 'updated_by'];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i A',
    ];

    
    public function rules(): HasMany
    {
       
        return $this->hasMany(SchemeRule::class, 'scheme_id', 'id');
    }
}
