<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeamlessUpiCollection extends Model
{
    protected $table = 'seamless_upi_collections';

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
        'upi_intent',
        'txn_order_id',
        'route',
        'response_code',
        'response_message',
        'response',
        'utr',
        'is_auto_settlement',
        'is_webhook_send',
        'status',
        'webhook_send_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
