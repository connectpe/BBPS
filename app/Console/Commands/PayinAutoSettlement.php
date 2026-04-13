<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayinAutoSettlement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payin:auto-settlement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto settlement Payin Orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Auto Settlement Started...');

        Log::info("Auto Settlement started");

        // dd(1);


        try {
            $time = 30;
            $eligibleUsers = DB::table('upi_collections')
                ->select('user_id')
                ->where('is_auto_settlement', '0')
                ->where('status', 'success')
                ->where('user_id', '!=', '')
                ->where('created_at', '<', now()->subHours(4))
                ->distinct()
                ->get();

            // dd($eligibleUsers);
            $count = 0;

            if ($eligibleUsers->isEmpty()) {
                $this->info('No pending settlements found.');
                return 0;
            }
            // dd($eligibleUsers);

            foreach ($eligibleUsers as $user) {
                // dd($user);
                $userId = $user->user_id;
                $cutoffTime = Carbon::now()->subMinutes($time);

                $alreadySettled = DB::table('user_settlements')
                    ->where('user_id', $userId)
                    ->where('created_at', '>', $cutoffTime)
                    ->exists();

                if ($alreadySettled) {
                    continue;
                }


                $totalAmount = DB::table('upi_collections')
                    ->where('user_id', $userId)
                    ->where('status', 'success')
                    ->where('is_auto_settlement', '0')
                    ->where('created_at', '<', now()->subHours(4))
                    ->sum('net_amount');

                $totalPaidAmount = DB::table('upi_collections')
                    ->where('user_id', $userId)
                    ->where('status', 'success')
                    ->where('is_auto_settlement', '0')
                    ->where('created_at', '<', now()->subHours(4))
                    ->sum('amount');

                // dd($totalAmount);
                $fee = DB::table('upi_collections')
                    ->where('user_id', $userId)
                    ->where('status', 'success')
                    ->where('is_auto_settlement', '0')
                    ->where('created_at', '<', now()->subHours(4))
                    ->sum('fee');

                $tax = DB::table('upi_collections')
                    ->where('user_id', $userId)
                    ->where('status', 'success')
                    ->where('is_auto_settlement', '0')
                    ->where('created_at', '<', now()->subHours(4))
                    ->sum('tax');

                // dd($totalAmount);

                if ($totalAmount <= 0) {
                    continue;
                }

                $user = DB::table('users')->where('id', $userId)->first();

                if (!$user) {
                    continue;
                }

                DB::beginTransaction();

                try {

                    $newPayingBalance = $user->payin_wallet_amount - $totalAmount;
                    // dd($newPayingBalance);
                    if ($newPayingBalance < 0) $newPayingBalance = 0;


                    $newPrimaryBalance = $user->transaction_amount + $totalAmount;
                    // dd($newPrimaryBalance);


                    DB::table('users')->where('id', $userId)->update([
                        'payin_wallet_amount' => $newPayingBalance,
                        'transaction_amount' => $newPrimaryBalance,
                        'updated_at' => now(),
                    ]);

                    DB::table('upi_collections')
                        ->where('user_id', $userId)
                        ->where('status', 'success')
                        ->where('is_auto_settlement', '0')
                        ->where('created_at', '<', now()->subHours(4))
                        ->update([
                            'is_auto_settlement' => '1',
                            'updated_at' => now(),
                        ]);


                    $settleID = 'SET' . time() . rand(10000, 99999);

                    DB::table('user_settlements')->insert([
                        'user_id' => $userId,
                        'connectpe_id' => '',
                        'settlement_ref_id' => $settleID,
                        'tax' => $tax,
                        'status' => 'success',
                        'fee' => $fee,
                        'amount' => $totalPaidAmount,
                        'net_amount' => $totalAmount,
                        'from_wallet' => 'paying_wallet',
                        'to_wallet' => 'primary_wallet',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $transaction_id = $this->generateUniqueTxnId();

                    // dd($transaction_id);

                    DB::table('ladgers')->insert([
                        'request_id' => $transaction_id,
                        'connectpe_id' => '',
                        'reference_no' => $settleID,
                        'user_id' => $userId,
                        'total_txn_amount' => '+' . $totalAmount,
                        'txn_amount' =>  $totalAmount,
                        'txn_type' => 'cr',
                        'tr_date' => now(),
                        'remarks' => $totalAmount . ' credited against ' . $user->transaction_amount,
                        'opening_balance' => $user->transaction_amount,
                        'closing_balanace' => $newPrimaryBalance,
                    ]);

                    DB::commit();
                    $count++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error settling user {$userId}: " . $e->getMessage());
                }
            }

            Log::info("$count} user(s) settled successfully.");

            $this->info("$count} user(s) settled successfully.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Auto Settlement Fail: " . $e->getMessage());
            return 1;
        }
    }

    private function generateUniqueTxnId(): string
    {
        do {
            $txnId = 'TXN' . strtoupper(Str::random(8)) . time();
        } while (
            DB::table('transactions')->where('txn_id', $txnId)->exists() ||
            DB::table('transactions')->where('txn_ref_id', $txnId)->exists()
        );

        return $txnId;
    }
}
