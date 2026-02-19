<?php

namespace App\Jobs;

use App\Helpers\MobiKwikHelper;
use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobikwikPaymentApiCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    private array $payload;
    private string $endpoint;
    private string $token;
    private string $type;

    public function __construct(string $endpoint, array $payload, string $token, string $type)
    {
        $this->endpoint = $endpoint;
        $this->payload  = $payload;
        $this->token    = $token;
        $this->type     = $type;
    }

    public function handle(): void
    {
        try {

            switch ($this->type) {

                case 'mobikwik':
                    $this->handleMobikwik();
                    break;

                default:
                    Log::warning('Unsupported payment type', [
                        'type' => $this->type,
                        'payload' => $this->payload
                    ]);
                    break;
            }

        } catch (\Throwable $e) {
            Log::error('Payment API Job Failed', [
                'error' => $e->getMessage(),
                'type' => $this->type,
                'payload' => $this->payload
            ]);
            throw $e; 
        }
    }

    
    private function handleMobikwik(): void
    {
        $mobikwik = new MobiKwikHelper();

        $response = $mobikwik->sendRequest(
            $this->endpoint,
            $this->payload,
            $this->token
        );

        if (!isset($response['data'])) {
            throw new \Exception('Invalid Mobikwik response');
        }

        DB::transaction(function () use ($response) {

           
            $transaction = Transaction::where('user_id', $this->payload['userid'])
                ->where('request_id', $this->payload['reqid'])
                ->where('status','processing')
                ->where('cron_status','1')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning('Transaction not found', $this->payload);
                return;
            }

            $status = strtoupper($response['data']['status'] ?? 'FAILED');

            if ($status === 'SUCCESS') {

                $transaction->update([
                    'status'      => 'success',
                    'utr'         => $response['data']['txId'] ?? null,
                    'opRefNo'     => $response['data']['reference_number'] ?? null,
                    'discountprice' => $response['data']['discountprice'] ?? 0,
                ]);

                TransactionHelper::sendCallback(
                    $transaction->user_id,
                    $transaction->request_id,
                    'success'
                );

            } elseif ($status === 'FAILED') {

                
                $this->payload['call'] = 'failed_order';

                dispatch(
                    new DebitBalanceUpdateJob(
                        $this->endpoint,
                        $this->payload,
                        $this->token
                    )
                )->delay(now()->addSeconds(5))
                 ->onQueue('recharge_process_queue');

            } else {
                
                Log::info('Mobikwik transaction pending', [
                    'request_id' => $this->payload['reqid']
                ]);
            }

        });
    }
}
