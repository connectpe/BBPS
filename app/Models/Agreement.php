<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected $fillable = ['file_path', 'status', 'updated_by'];

    public function updateBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
