<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'user_id',
        'service_id',
        'description',
        'status',
        'resolved_at',
        'remark',
        'attachment_path',
        'priority',
        'complaints_category',
        'updated_by ',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }
}
