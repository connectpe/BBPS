<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'operator_id', 'circle_id',
        'amount', 'txn_type', 'reference_no', 'status'
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
