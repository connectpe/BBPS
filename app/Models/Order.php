<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_ref_id',
        'contact_id',
        'provider_id',
        'mode_id',
        'service_id',
        'connectpe_id',
        'order_ref_id',
        'user_id',
        'currency',
        'amount',
        'fee',
        'tax',
        'total_amount',
        'mode',
        'purpose',
        'utr_no',
        'narration',
        'remark',
        'status',
        'status_code',
        'status_response',
        'failed_status_code',
        'failed_message',
        'failed_at',
        'txn_refunded',
        'txn_refunded_at',
        'ip',
        'user_agent',
        'is_api_call',
        'is_cron',
        'cron_date',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'txn_refunded' => 'decimal:2',
        'failed_at' => 'datetime',
        'txn_refunded_at' => 'datetime',
        'cron_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'mode_id', 'mode_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
