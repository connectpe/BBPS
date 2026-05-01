<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use App\Models\Ladger;
use App\Models\Transaction;
use App\Models\UserService;
use App\Models\Ledger;
use App\Models\RechargeOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RechargeDebitBalanceAndStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 400;
    public $timeout = 14400;
    public $failOnTimeout = true;

    private $connectpeId, $userId, $call, $serviceId, $payload, $errorDesc, $statusCode, $status;

    public function __construct($connectpeId, $userId, $call, $serviceId, $payload, $errorDesc = "", $statusCode = "", $status = "")
    {
        $this->connectpeId = $connectpeId;
        $this->userId = $userId;
        $this->call = $call;
        $this->serviceId = $serviceId;
        $this->payload = $payload;
        $this->errorDesc = $errorDesc;
        $this->statusCode = $statusCode;
        $this->status = $status;
    }

    public function handle(): void
    {
        try {
            Log::info('Inter in RechargeBalanceDebitAndStatusUpdateJob', [
                'call_type' => $this->call,
                'user_id' => $this->userId,
                'connectpe_id' => $this->connectpeId,
                'service_id' =>  $this->serviceId
            ]);

            if ($this->call == 'balance_debit') {
                $OrderData = RechargeOrder::select('user_id', 'payment_ref_id', 'connectpe_id')
                    ->where(['is_cron' => '0', 'status' => 'queue', 'user_id' => $this->userId, 'connectpe_id' => $this->connectpeId])
                    ->first();

                Log::info('order data', ['data' => $OrderData]);
                if (isset($OrderData) && !empty($OrderData)) {

                    $provider = CommonHelper::getProviderSlug($this->userId, $this->serviceId);
                    $providerSlug = $provider['provider_slug'];
                    $providerId =  $provider['provider_id'];

                    $lockeOrder = TransactionHelper::moveRechargeOrderToPending($OrderData->user_id, $OrderData->payment_ref_id, $OrderData->connectpe_id, $providerId);

                    Log::info('lock order', ['data' => $lockeOrder]);
                    if ($lockeOrder['status'] && isset($OrderData)) {
                        Log::info('Going to Enter in OrderProcessApiCallJob');
                        dispatch(new \App\Jobs\MobikwikPaymentApiCallJob($OrderData->connectpe_id, $OrderData->user_id, $providerSlug, $providerId, $this->payload))->delay(rand(2, 7))->onQueue('recharge_process_queue');
                    } else {
                        $errorDesc = $lockeOrder['message'];
                        $statusCode = '';
                        $txn = CommonHelper::getRandomString('txn', false);


                        if ($errorDesc == 'debit_balance_failed') {
                            dispatch(new RechargeDebitBalanceAndStatusUpdateJob($OrderData->connectpeId, $OrderData->userId, 'balance_debit', $OrderData->serviceId, $this->payload, '', '', ''))->onQueue('recharge_debit_queue');
                        }

                        DB::select("CALL OrderStatusUpdate('" . $OrderData->payment_ref_id . "', $OrderData->user_id,
                        'failed', '" . $txn . "', '" . $errorDesc . "', '" . $statusCode . "','" . "', @json)");
                        $results = DB::select('select @json as json');
                        $response = json_decode($results[0]->json, true);
                        if ($response['status'] == '1') {
                            TransactionHelper::sendCallback($OrderData->user_id, $OrderData->payment_ref_id, 'failed');
                        }
                    }
                }
            } else if ($this->call == 'failed_order') {
                Log::info('RechargeBalanceDebitAndStatusUpdateJob failed');
                Log::info('failed_order', ['user_id' => $this->userId, 'transaction_no' => $this->payload['payment_ref_id']]);
                $OrderData = RechargeOrder::select('payment_ref_id ', 'user_id')
                    ->where(['status' => 'pending', 'user_id' => $this->userId, 'connectpe_id' =>  $this->connectpeId])
                    ->first();

                Log::info('failed_order:OrderData', ['data' => json_encode($OrderData), 'connectpe_id' => $this->connectpeId]);
                if (isset($OrderData) && !empty($OrderData)) {
                    $txn = CommonHelper::getRandomString('txn', false);
                    DB::select("CALL OrderStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id,'" . $this->status . "', '" . $txn . "', '" . $this->errorDesc . "', '" . $this->statusCode . "','" . "', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                    Log::info('After OrderStatusUpdate:', [$results[0]->json]);
                    Log::info('Response payoutBalanceAndStatusUpdate:', $response);
                    if ($response['status'] == '1') {
                        TransactionHelper::sendCallback($OrderData->user_id, $OrderData->transaction_no,  $this->status);
                    }
                }
            }
        } catch (\Exception  $e) {
            Log::error('Payout Job Error: ' . $e->getMessage());
            Log::error('Error at Line: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());

            $fileName = 'public/orderDeadlock' . $this->connectpeId . '.txt';
            Storage::disk('local')->append($fileName, $e->getMessage() . " | " . date('H:i:s'));
        }
    }
}
