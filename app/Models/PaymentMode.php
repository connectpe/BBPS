<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    protected $fillable = [
        'mode_id',
        'service_id',
        'mode_name',
        'mode_slug',
        'min_order_value',
        'max_order_value',
        'tax_value',
        'is_active',
        'updated_by',
    ];
}
