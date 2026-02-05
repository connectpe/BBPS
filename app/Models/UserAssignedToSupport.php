<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignedToSupport extends Model
{
    protected $fillable = [
        'user_id',
        'assined_to', 
        'updated_by',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public $timestamps = true;

  
    protected $casts = [
        'created_at' => 'datetime:d-m-Y h:i A',
    ];    


    public function assigned_support()
    {
        return $this->belongsTo(User::class, 'assined_to');
    }

    
    public function creator()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}