<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSettlement extends Model
{
    protected $fillable = [
        'user_id',
        'settlement_ref_id',
        'mode',
        'amount',
        'fee',
        'tax',
        'net_amount',
        'account_number',
        'account_ifsc',
        'beneficiary_name',
        'status',
        'is_balance_debited',
        'from_wallet',
        'to_wallet'
    ];
}
