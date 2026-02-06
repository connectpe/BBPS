<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ladger extends Model
{
    protected $fillable = ['reference_no', 'request_id', 'connectpe_id', 'user_id', 'txn_amount', 'total_txn_amount', 'txn_date', 'txn_type', 'service_id', 'opening_balance', 'closing_balanace', 'remarks'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }
}
