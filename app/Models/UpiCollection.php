<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpiCollection extends Model
{
    protected $fillable = [
        'user_id',
        'cust_txn_id',
        'connectpe_order_id',
        'cust_name',
        'cust_email',
        'cust_mobile',
        'amount',
        'fee',
        'tax',
        'net_amount',
        'qr_intent',
        'npci_txn_id',
        'txn_order_id',
        'txn_id',
        'type',
        'root',
        'res_code',
        'res_message',
        'response',
        'utr',
        'status',
        'is_auto_settlement',
        'is_webhook_sent',
        'webhook_sent_at',
        'is_txn_credited',
        'txn_credited_at',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'response' => 'array',
        'is_auto_settlement' => 'boolean',
        'is_webhook_sent' => 'boolean',
        'is_txn_credited' => 'boolean',
        'webhook_sent_at' => 'datetime',
        'txn_credited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}