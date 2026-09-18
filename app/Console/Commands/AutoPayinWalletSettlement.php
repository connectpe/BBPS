<?php

namespace App\Console\Commands;

use App\Models\SeamlessUpiCollection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPayinWalletSettlement extends Command
{
    protected $signature = 'app:update-payin-wallet';

    protected $description = 'Auto credit payin wallet from successful seamless UPI collections';

    public function handle()
    {
        DB::transaction(function () {

            $users = SeamlessUpiCollection::select('user_id',
                DB::raw('SUM(net_amount) as total_amount')
            )
                ->where('status', 'success')
                ->where('is_auto_settlement', 0)
                ->groupBy('user_id')
                ->get();

            foreach ($users as $user) {
                User::where('id', $user->user_id)->update(['payin_wallet_amount' => $user->total_amount]);
                Log::info('Payin Wallet Updated', [
                    'user_id' => $user->user_id,
                    'credited_amount' => $user->total_amount,
                ]);

            }
        });

        Log::info('Auto Payin Wallet Settlement Command Executed');

        $this->info('Payin wallet updated successfully.');
    }
}
