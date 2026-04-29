<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeOrder extends Model
{
    protected $fillable = ['user_id', 'provider_id', 'service_id', 'connectpe_id', 'request_id', 'payment_ref_id', 'transaction_id', 'utr', 'connection_no', 'operator_id', 'circle_id', 'plan_type', 'customer_mobile', 'agent_id', 'remitter_name', 'payment_mode', 'payment_account_info', 'recharge_type', 'amount', 'fee', 'tax', 'net_amount', 'success_response', 'failed_response', 'failed_message', 'failed_at', 'transaction_reversed', 'transaction_reversed_at', 'narration', 'remark', 'ip', 'user_agent', 'is_api_call', 'is_cron', 'cron_date', 'status'];
}
