<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Http\Controllers\CommonController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Order;
use App\Helpers\TransactionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PayoutBalanceDebitAndStatusUpdateJob implements ShouldQueue
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

    private $orderRefId, $userId, $serviceId, $call, $getServicePkId, $errorDesc, $statusCode, $utr, $status;

    public function __construct($orderRefId, $userId, $serviceId, $call, $getServicePkId = "", $errorDesc = "", $statusCode = "", $utr = "", $status = "")
    {
        $this->orderRefId = $orderRefId;
        $this->userId = $userId;
        $this->serviceId = $serviceId;
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
        try {
            Log::info('Inter in PayoutBalanceDebitAndStatusUpdateJob');
            if ($this->call == 'balance_debit') {
                $OrderData = Order::select('user_id', 'order_ref_id', 'contact_id')
                    ->where(['cron_status' => '0', 'status' => 'queued', 'user_id' => $this->userId, 'order_ref_id' => $this->orderRefId])
                    ->first();

                if (isset($OrderData) && !empty($OrderData)) {

                    $provider = CommonHelper::getProviderSlug($this->userId, $this->serviceId);
                    $providerSlug = $provider['provider_slug'];
                    $providerId =  $provider['provider_id'];

                    $lockeOrder = TransactionHelper::moveOrderToProcessingByOrderId($OrderData->user_id, $OrderData->order_ref_id, $providerId);


                    if ($lockeOrder['status'] && isset($OrderData)) {
                        dispatch(new \App\Jobs\OrderProcessApiCallJob($OrderData->order_ref_id, $OrderData->user_id, $providerSlug, $providerId))->delay(rand(2, 7))->onQueue('payout_process_queue');
                        Log::info('Enter in OrderProcessApiCallJob');
                    } else {
                        $errorDesc = $lockeOrder['message'];
                        $statusCode = '';
                        $txn = CommonHelper::getRandomString('txn', false);
                        $utr = '';

                        if ($errorDesc == 'debit_balance_failed') {
                            dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($OrderData->order_ref_id, $OrderData->user_id, 'balance_debit', '', '', '', '', ''))->delay(rand(5, 10))->onQueue('payout_debit_queue');
                        }

                        DB::select("CALL OrderStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id,
                        'failed', '" . $txn . "', '" . $errorDesc . "', '" . $statusCode . "','" . $utr . "', @json)");
                        $results = DB::select('select @json as json');
                        $response = json_decode($results[0]->json, true);
                        if ($response['status'] == '1') {
                            TransactionHelper::sendCallback($OrderData->user_id, $OrderData->order_ref_id, 'failed');
                        }
                    }
                }
            } else if ($this->call == 'failed_order') {
                Log::info('failed_order', ['user_id' => $this->userId, 'transaction_no' => $this->orderRefId]);
                $OrderData = Order::select('transaction_no', 'user_id')
                    ->where(['status' => 'processing', 'user_id' => $this->userId, 'transaction_no' => $this->orderRefId])
                    ->first();

                Log::info('failed_order:OrderData', ['data' => json_encode($OrderData), 'order_ref_id' => $this->orderRefId]);
                if (isset($OrderData) && !empty($OrderData)) {
                    $txn = CommonHelper::getRandomString('txn', false);
                    DB::select("CALL OrderStatusUpdate('" . $OrderData->order_ref_id . "', $OrderData->user_id,'" . $this->status . "', '" . $txn . "', '" . $this->errorDesc . "', '" . $this->statusCode . "','" . $this->utr . "', @json)");
                    $results = DB::select('select @json as json');
                    $response = json_decode($results[0]->json, true);
                    Log::info('After OrderStatusUpdate:', [$results[0]->json]);
                    Log::info('Response payoutBalanceAndStatusUpdate:', $response);
                    if ($response['status'] == '1') {
                        // if ($OrderData->area == '00') {
                        //     BulkPayoutDetail::payStatusUpdate($OrderData->batch_id, 'failed', $OrderData->order_ref_id, $this->errorDesc, $this->utr);
                        //     BulkPayout::updateStatusByBatch($OrderData->batch_id, array('status' => 'processed'));
                        // }
                        TransactionHelper::sendCallback($OrderData->user_id, $OrderData->transaction_no,  $this->status);
                    }
                }
            }
        } catch (\Exception  $e) {
            $fileName = 'public/orderDeadlock' . $this->orderRefId . '.txt';
            Storage::disk('local')->append($fileName, $e . date('H:i:s'));
        }
    }
}
