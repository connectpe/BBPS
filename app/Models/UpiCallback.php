<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UpiCallback extends Model
{
    use HasFactory;

    protected $table = 'upi_callbacks';

    protected $fillable = [
        'txn_id',
        'txn_order_id',
        'amount',
        'root',
        'utr',
        'status',
        'message',
        'response',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response' => 'array',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

