<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'operator_id', 'circle_id',
        'amount', 'transaction_type','request_id' ,'mobile_number', 'payment_ref_id','recharge_type','connectpe_id','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }
}
