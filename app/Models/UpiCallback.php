<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpiCallback extends Model
{
    protected $fillable = ['txn_id', 'txn_order_id', 'amount', 'root', 'utr', 'status', 'message', 'response', 'updated_by'];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
