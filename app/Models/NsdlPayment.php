<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NsdlPayment extends Model
{
    protected $fillable = ['user_id', 'service_id', 'mobile_no', 'amount', 'transaction_id', 'utr', 'order_id', 'status', 'updated_by'];
}
