<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'service_id',
        'complaints_category',
        'priority',
        'remark',
        'resolved_at',
        'attachment_file',
        'status',
        'description',
        'updated_by',
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

    public function category()
    {
        return $this->belongsTo(ComplaintsCategory::class, 'complaints_category');
    }
}
