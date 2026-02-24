<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('orders')->insert([
            [
                'user_id' => 11,
                'service_id' => 1,
                'provider_id' => 1,

                'connectpe_id' => 'CP-10001',
                'transaction_no' => 'TXN-90001',
                'client_txn_id' => 'CL-77881',

                'amount' => 500.00,
                'utr_no' => 'UTR123456',
                'fee' => '5.00',
                'tax' => '5.00',
                'total_amount' => 510.00,

                'account_no' => '123456789012',
                'ifsc_code' => 'HDFC0001234',
                'bank_name' => 'HDFC Bank',
                'beneficiary_name' => 'Test User',

                'mode' => 'IMPS',
                'purpose' => 'Vendor Settlement',
                'status' => 'processed',
                'currency' => 'INR',

                'status_code' => '200',
                'is_api_call' => '1',
                'is_cron' => '0',
                'cron_date' => null,
                'failed_msg' => null,
                'fee_type' => 'flat',
                'remark' => 'Success payout',

                'updated_by' => 11,

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
