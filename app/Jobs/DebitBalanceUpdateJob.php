<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use App\Models\UserService;
use App\Models\Ledger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DebitBalanceUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 300;
    public $failOnTimeout = true;

    private string $endpoint;
    private array $payload;
    private string $token;

    public function __construct(string $endpoint, array $payload, string $token)
    {
        $this->endpoint = $endpoint;
        $this->payload  = $payload;
        $this->token    = $token;
    }

    public function handle(): void
    {
        try {
            match ($this->payload['call']) {
                'balance_debit' => $this->handleDebit(),
                'failed_order'  => $this->handleFailedOrder(),
                default         => Log::warning('Unknown job call', $this->payload),
            };
        } catch (\Throwable $e) {
            Log::error('DebitBalanceUpdateJob failed', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);

            throw $e;
        }
    }

    /**
     * Debit wallet and initiate payment 
     */
    private function handleDebit(): void
    {
        DB::transaction(function () {

            $transaction = Transaction::where([
                'user_id'    => $this->payload['userid'],
                'request_id' => $this->payload['reqid'],
                'status'     => 'queued',
            ])->lockForUpdate()->first();

            if (!$transaction) {
                Log::warning('Transaction not found for debit', $this->payload);
                return;
            }

            $userService = UserService::where(['user_id', $this->payload['userid'],'service_id'=>$this->payload['serviceId'],'is_active'=>'1'])->first();
            if (!$userService) {
                throw new \Exception('User service is not enable right now');
            }

            // Wallet debit
            DB::statement(
                "CALL debitAmountFromUserWallet(?, ?, ?, ?)",
                [
                    $this->payload['userid'],
                    $this->payload['amt'],
                    $this->payload['serviceId'],
                    false
                ]
            );

            $result = DB::selectOne('SELECT @json AS json');
            $response = json_decode($result->json, true);

            if (!$response['success']) {
                throw new \Exception('Wallet debit failed');
            }

            Ledger::create([
                'reference_no'     => $this->payload['paymentRefID'],
                'request_id'       => $this->payload['reqid'],
                'connectpe_id'     => $this->payload['connectpeId'],
                'user_id'          => $this->payload['userid'],
                'txn_amount'       => '-' . $response['amount'],
                'txn_type'         => 'dr',
                'service_id'       => $userService->service_id,
                'opening_balance'  => $response['opening_balance'],
                'closing_balance'  => $response['remaining_balance'],
                'remark'           => 'Recharge debit',
            ]);

            $transaction->cron_status = '1';
            $transation->status = 'processing';

            $transaction->save();

            dispatch(
                new MobikwikPaymentApiCallJob(
                    $this->endpoint,
                    $this->payload,
                    $this->token
                )
            )->onQueue('recharge_process_queue');
        });
    }

    /**
     *      
     *     Credit wallet if transaction failed any reason
     * 
     * 
     */
    private function handleFailedOrder(): void
    {
        DB::transaction(function () {

            $transaction = Transaction::where([
                'user_id'    => $this->payload['userid'],
                'request_id' => $this->payload['reqid'],
                'status'     => 'processing',
                'cron_status' => '1'
            ])->lockForUpdate()->first();

            if (!$transaction) {
                return;
            }

            $userService = UserService::where('user_id', $this->payload['userid'])->first();

            DB::statement(
                "CALL debitAmountFromUserWallet(?, ?, ?, ?)",
                [
                    $this->payload['userid'],
                    $this->payload['amt'],
                    $userService->service_id,
                    true
                ]
            );

            $result = DB::selectOne('SELECT @json AS json');
            $response = json_decode($result->json, true);

            if (!$response['success']) {
                throw new \Exception('Wallet credit failed');
            }

            Ledger::create([
                'reference_no'     => $this->payload['paymentRefID'],
                'request_id'       => $this->payload['reqid'],
                'connectpe_id'     => $this->payload['connectpeId'],
                'user_id'          => $this->payload['userid'],
                'txn_amount'       => '+' . $response['amount'],
                'txn_type'         => 'cr',
                'service_id'       => $userService->service_id,
                'opening_balance'  => $response['opening_balance'],
                'closing_balance'  => $response['remaining_balance'],
                'remark'           => 'Recharge refund',
            ]);

            $transaction->update(['status' => 'failed']);

            TransactionHelper::sendCallback(
                $transaction->user_id,
                $transaction->request_id,
                'failed'
            );
        });
    }
}
