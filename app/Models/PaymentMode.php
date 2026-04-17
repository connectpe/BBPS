<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    protected $fillable = ['product_id', 'service_id', 'name', 'slug', 'min_order_value', 'max_order_value', 'type', 'is_active', 'tax_value', 'updated_by'];
}
