<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'provider_id',
        'connectpe_id',
        'transaction_no',
        'client_txn_id',
        'amount',
        'utr_no',
        'fee',
        'tax',
        'total_amount',
        'mode',
        'purpose',
        'status',
        'currency',
        'status_code',
        'is_api_call',
        'is_cron',
        'cron_date',
        'failed_msg',
        'fee_type',
        'remark',
        'updated_by',
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
}
