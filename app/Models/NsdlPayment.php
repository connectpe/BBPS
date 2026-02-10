<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GlobalService;

class NsdlPayment extends Model
{
    use HasFactory;

    protected $table = 'nsdl_payments';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'mobile_no',
        'amount',
        'transaction_id',
        'utr',
        'order_id',
        'status',
        'updated_by',
    ];


    protected $casts = [
        'amount' => 'decimal:2',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function service()
    {
        return $this->belongsTo(GlobalService::class, 'service_id');
    }


    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
