<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;


class PayinAutoSettlement extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payin:auto-settlement';

    /**
     * The console command description.
     */
    protected $description = 'Auto settle all successful Payin transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Auto Settlement Started...');

        Log::info('Payin Auto Settlement Started');

        try {

            $today = Carbon::today();

            $eligibleUsers = DB::table('seamless_upi_collections')
                ->select('user_id')
                ->where('status', 'success')
                ->where('is_auto_settlement', '0')
                ->whereNotNull('user_id')
                ->where('user_id', '!=', '')
                ->where('created_at', '<', $today)
                ->distinct()
                ->get();

            if ($eligibleUsers->isEmpty()) {

                $this->info('No pending settlements found.');

                Log::info('Payin Auto Settlement: No pending settlements found.');

                return 0;
            }

            $count = 0;

            foreach ($eligibleUsers as $eligibleUser) {

                $userId = $eligibleUser->user_id;

                $transactions = DB::table('seamless_upi_collections')
                    ->where('user_id', $userId)
                    ->where('status', 'success')
                    ->where('is_auto_settlement', '0')
                    ->where('created_at', '<', $today);

                $totalPaidAmount = (float) $transactions->sum('amount');

                $fee = (float) $transactions->sum('fee');

                $tax = (float) $transactions->sum('tax');

                $totalAmount = (float) $transactions->sum('net_amount');

                if ($totalAmount <= 0) {
                    continue;
                }

                $user = DB::table('users')
                    ->where('id', $userId)
                    ->first();

                if (!$user) {
                    Log::warning('Auto Settlement: User not found', [
                        'user_id' => $userId,
                    ]);

                    continue;
                }

                DB::beginTransaction();

                try {

                    $openingPayinBalance = (float) $user->payin_wallet_amount;

                    $openingPrimaryBalance = (float) $user->transaction_amount;

                    $newPayinBalance = $openingPayinBalance - $totalAmount;

                    if ($newPayinBalance < 0) {

                        Log::warning('Payin wallet balance is insufficient for settlement', [
                            'user_id' => $userId,
                            'payin_balance' => $openingPayinBalance,
                            'settlement_amount' => $totalAmount,
                            'shortfall_amount' => $openingPayinBalance - $totalAmount,
                        ]);

                        $newPayinBalance = 0;
                    }

                    $newPrimaryBalance = $openingPrimaryBalance + $totalAmount;

                    DB::table('users')
                        ->where('id', $userId)
                        ->update([
                            'payin_wallet_amount' => $newPayinBalance,
                            'transaction_amount' => $newPrimaryBalance,
                            'updated_at' => now(),
                        ]);

                    $settleID = 'SET' . time() . rand(10000, 99999);

                    DB::table('user_settlements')->insert([
                        'user_id' => $userId,
                        'settlement_ref_id' => $settleID,
                        'tax' => $tax,
                        'fee' => $fee,
                        'amount' => $totalPaidAmount,
                        'net_amount' => $totalAmount,
                        'status' => 'success',
                        'from_wallet' => 'paying_wallet',
                        'to_wallet' => 'primary_wallet',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $transactionId = $this->generateUniqueTxnId();

                    DB::table('ladgers')->insert([
                        'request_id' => $transactionId,
                        'reference_no' => $settleID,
                        'user_id' => $userId,
                        'total_txn_amount' => '+' . $totalAmount,
                        'txn_amount' => $totalAmount,
                        'txn_type' => 'cr',
                        'txn_date' => now(),
                        'remarks' => $totalAmount . ' credited from payin wallet',
                        'opening_balance' => $openingPrimaryBalance,
                        'closing_balanace' => $newPrimaryBalance,
                    ]);


                    DB::table('seamless_upi_collections')
                        ->where('user_id', $userId)
                        ->where('status', 'success')
                        ->where('is_auto_settlement', '0')
                        ->where('created_at', '<', $today)
                        ->update([
                            'is_auto_settlement' => '1',
                            'updated_at' => now(),
                        ]);

                    DB::commit();

                    $count++;

                    $this->info(
                        "User {$userId} settled successfully. " .
                            "Amount: {$totalAmount}"
                    );

                    Log::info('Payin Auto Settlement Successful', [
                        'user_id' => $userId,
                        'settlement_ref_id' => $settleID,
                        'amount' => $totalAmount,
                        'gross_amount' => $totalPaidAmount,
                        'fee' => $fee,
                        'tax' => $tax,
                    ]);
                } catch (\Throwable $e) {

                    DB::rollBack();

                    $this->error(
                        "Error settling user {$userId}: " .
                            $e->getMessage()
                    );

                    Log::error('Payin Auto Settlement Failed', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Payin Auto Settlement Completed', [
                'users_settled' => $count,
            ]);

            $this->info(
                "{$count} user(s) settled successfully."
            );

            return 0;
        } catch (\Throwable $e) {

            Log::error('Payin Auto Settlement Failed', [
                'error' => $e->getMessage(),
            ]);

            $this->error(
                'Auto Settlement Failed: ' .
                    $e->getMessage()
            );

            return 1;
        }
    }

    /**
     * Generate unique transaction ID.
     */
    private function generateUniqueTxnId(): string
    {
        do {
            $txnId = 'TXN' . strtoupper(Str::random(8)) . time();
        } while (
            DB::table('ladgers')
            ->where('reference_no', $txnId)
            ->exists()
            ||
            DB::table('ladgers')
            ->where('request_id', $txnId)
            ->exists()
        );

        return $txnId;
    }
}
