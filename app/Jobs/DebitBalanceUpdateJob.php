<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DebitBalanceUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 400;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 14400;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $requestId, $reference_number, $call, $mobile_number,$payment_ref_id,$payment_account_info,$connectpe_id,$recharge_type,$amount, $errorDesc, $statusCode, $utr, $status;

    public function __construct()
    {
        $this->orderRefId = $orderRefId;
        $this->userId = $userId;
        $this->call = $call;
        $this->getServicePkId = $getServicePkId;
        $this->errorDesc = $errorDesc;
        $this->statusCode = $statusCode;
        $this->utr = $utr;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    
    public function handle(): void
    {
         //Scheduled S0036 //Queued E0520
        try {

            if ($this->call == 'balance_debit') {
                $OrderData = Transaction::select('user_id', 'reference_number','contact_id')
                    ->where(['cron_status' => '0', 'status' => 'queued', 'user_id' => $this->userId, 'order_ref_id' => $this->orderRefId])
                    ->whereIn('area', ['11', '22'])
                    ->first();
               
                if (isset($OrderData) && !empty($OrderData)) {
                    $userConfigGetRoute = CommonHelper::getPayoutRouteUsingUserId($OrderData->user_id, 'api');
                    if ($userConfigGetRoute['status']) {
                        $types = $userConfigGetRoute['slug'];
                        $integrationId = $userConfigGetRoute['integration_id'];
                    } else {
                        $route = CommonHelper::defaultPayoutRoute('api_payout_route');
                        $types = $route['slug'];
                        $integrationId = $route['integration_id'];
                    }

                    $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($OrderData->user_id, $OrderData->order_ref_id, $integrationId);
                    if ($lockeOrder['status'] && isset($OrderData)) {
                        dispatch(new \App\Jobs\OrderProcessApiCallJob($OrderData->order_ref_id, $OrderData->user_id, $types, $integrationId))->delay(rand(2, 7))->onQueue('payout_process_queue');
                    } else {
                        $errorDesc = $lockeOrder['message'];
                        $statusCode = '';
                        $txn = CommonHelper::getRandomString('txn', false);
                        $utr = '';

                        $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $OrderData->user_id)->where('service_id', PAYOUT_SERVICE_ID)->first();

                        if ($errorDesc == 'debit_balance_failed') {
                            dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($OrderData->order_ref_id, $OrderData->user_id, 'balance_debit', '', '', '', '', ''))->delay(rand(5, 10))->onQueue('payout_debit_queue');
                        }

                        DB::select("CALL OrderStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $getServicePkId->id, 'failed', '" . $txn . "', '" . $errorDesc . "', '" . $statusCode . "','" . $utr . "', @json)");
                        $results = DB::select('select @json as json');
                        $response = json_decode($results[0]->json, true);
                        if ($response['status'] == '1') {
                            TransactionHelper::sendCallback($OrderData->user_id, $OrderData->order_ref_id, 'failed');
                        }
                    }
                }
            } else if ($this->call == 'failed_order') {
                \Log::info('failed_order',['user_id' => $this->userId, 'order_ref_id' => $this->orderRefId]);
                $OrderData = Order::select('order_ref_id', 'user_id', 'batch_id', 'area')
                    ->where(['status' => 'processing', 'user_id' => $this->userId, 'order_ref_id' => $this->orderRefId])
                    ->whereIn('orders.area', ['00', '11', '22'])
                    ->first();
                    \Log::info('failed_order:OrderData',['data' => json_encode($OrderData), 'order_ref_id' => $this->orderRefId]);
                if (isset($OrderData) && !empty($OrderData)) {
                    $txn = CommonHelper::getRandomString('txn', false);
                    \Log::info('OrderStatusUpdate:',[$OrderData->order_ref_id,$this->status,$this->utr,$this->getServicePkId,$txn,$this->errorDesc,$this->statusCode]);
                    DB::select("CALL OrderStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id, $this->getServicePkId, '" . $this->status . "', '" . $txn . "', '" . $this->errorDesc . "', '" . $this->statusCode . "','" . $this->utr . "', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                    \Log::info('After OrderStatusUpdate:',[$results[0]->json]);
                    \Log::info('Response payoutBalanceAndStatusUpdate:',$response);
                    if ($response['status'] == '1') {
                        if ($OrderData->area == '00') {
                            BulkPayoutDetail::payStatusUpdate($OrderData->batch_id, 'failed', $OrderData->order_ref_id, $this->errorDesc, $this->utr);
                            BulkPayout::updateStatusByBatch($OrderData->batch_id, array('status' => 'processed'));
                        }
                        TransactionHelper::sendCallback($OrderData->user_id, $OrderData->order_ref_id,  $this->status);
                    }
                }
            }
        } catch (\Exception  $e) {
            $fileName = 'public/orderDeadlock'. $this->orderRefId . '.txt';
            Storage::disk('local')->append($fileName, $e . date('H:i:s'));
        }
    }
}
