<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class OrderProcessApiCallJob implements ShouldQueue
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

    private $orderRefId,$userId,$types,$integrationId;
    public function __construct()
    {
        $this->orderRefId = $orderRefId;
        $this->userId = $userId;
        $this->types = $types;
        $this->integrationId = $integrationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $orderCount = 0;
            $types = $this->types;
            $integrationId = $this->integrationId;
            // $Order = Order::select('*')
            //     ->join('contacts', 'contacts.contact_id', 'orders.contact_id')
            //     ->whereIn('orders.area', ['11', '22'])
            //     ->where('orders.cron_status', '1')
            //     ->where('orders.is_api_call', '0')
            //     ->where('orders.user_id', $this->userId)
            //     ->where('orders.order_ref_id', $this->orderRefId)
            //     ->orderBy('orders.id', 'asc')
            //     ->first();

            $orderUpdate = DB::table('orders')
                ->where('user_id', $this->userId)
                ->where('transaction_no', $this->orderRefId)
                ->where('cron_status', '1')
                ->update(['is_api_call' => '1', 'cron_date' => date('Y-m-d H:i:s')]);

            if (isset($Order) && ! empty($Order) && isset($orderUpdate) && ! empty($orderUpdate)) {

                switch ($types) {

                    case 'idfcpayout':
                        $orderCount += 1;
                        $idfcPayout = app(\App\Helpers\IdfcPayoutHelper::class);

                        $requestTransfer = $idfcPayout->fundTransfer($Order);
                        \Log::info('response of idfc', [$requestTransfer]);

                        $orderStatus = 'pending';

                        if (isset($requestTransfer['initiateAuthGenericFundTransferAPIResp'])) {

                            $resp = $requestTransfer['initiateAuthGenericFundTransferAPIResp'];
                            $meta = $resp['metaData'] ?? [];
                            $resource = $resp['resourceData'] ?? [];

                            $status = $meta['status'] ?? 'ERROR';
                            $message = $meta['message'] ?? '';
                            $bank_reference = $resource['transactionReferenceNo'] ?? '';
                            $transactionID = $resource['transactionID'] ?? '';
                            $beneficiaryName = $resource['beneficiaryName'] ?? '';
                            $resourceStatus = $resource['status'] ?? '';
                            $statusCode = $meta['code'] ?? '';

                            if (
                                str_contains($message, 'IMPS: CLEARED BAL/FUNDS/DP NOT AVAILABLE') ||
                                str_contains($message, 'ACCT WILL BE OVERDRAWN')
                            ) {
                                $message = 'Currently service is down. Please contact the administrator.';
                            }

                            DB::table('orders')
                                ->where(['user_id' => $Order->user_id, 'transaction_no' => $Order->transaction_no])
                                ->update(['cron_date' => now()]);

                            if ($status === 'SUCCESS' && in_array($resourceStatus, ['ACPT', 'SUCCESS'])) {

                                $statusCode = 200;
                                $message = $message ?: 'Transaction processed successfully.';

                                DB::select("CALL OrderStatusProcessedUpdate('".$Order->order_ref_id."', $Order->user_id, 'processed', '".$message."', '".$statusCode."', '".$bank_reference."', @json)");
                                $results = DB::select('select @json as json');
                                $response = json_decode($results[0]->json, true);

                                if (($response['status'] ?? '0') == '1') {
                                    TransactionHelper::sendCallback($Order->user_id, $Order->order_ref_id, 'processed');
                                }

                            } elseif ($status === 'SUCCESS' && strtoupper($resourceStatus) === 'ACPT') {

                                DB::table('orders')
                                    ->where(['user_id' => $Order->user_id, 'order_ref_id' => $Order->order_ref_id])
                                    ->update([
                                        'payout_id' => $transactionID,
                                        'cron_date' => now(),
                                    ]);

                            } elseif($status == 'ERROR') {
                                $refId = isset($requestTransfer['initiateAuthGenericFundTransferAPIResp']) ? $requestTransfer['initiateAuthGenericFundTransferAPIResp'] : null;
                                DB::table('orders')
                                    ->where(['user_id' => $Order->user_id, 'order_ref_id' => $Order->order_ref_id])
                                    ->update(['payout_id' => $refId, 'cron_date' => date('Y-m-d H:i:s')]);

                                $errorDesc = isset($message) ? $message : '';
                                $utr = isset($transactionID) ? $transactionID : '';
                                $getServicePkId = DB::table('user_services')->select('id')->where('user_id', $Order->user_id)->where('service_id', PAYOUT_SERVICE_ID)->first();

                                dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($Order->order_ref_id, $Order->user_id, 'failed_order', $getServicePkId->id, $errorDesc, '', $utr, 'failed'))
                                    ->onQueue('payout_update_queue');
                            } else {

                                $errorDesc = $message;
                                $statusCode = '';
                                $utr = $bank_reference;

                                $getServicePkId = DB::table('user_services')
                                    ->select('id')
                                    ->where('user_id', $Order->user_id)
                                    ->where('service_id', PAYOUT_SERVICE_ID)
                                    ->first();

                                dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob(
                                    $Order->transaction_no,
                                    $Order->user_id,
                                    'failed_order',
                                    $getServicePkId->id ?? 0,
                                    $errorDesc,
                                    $statusCode,
                                    $utr,
                                    'failed'
                                ))->onQueue('payout_update_queue');
                            }
                        } else {

                            \Log::error('Invalid IDFC response structure', [$requestTransfer]);
                        }

                        break;

                }
            }
        } catch (\Exception  $e) {
            // Storage::disk('local')->append($fileName, $e . date('H:i:s'));
            \Log::info('After dispatching OrderProcessApiCallJob', ['error' => $e->getMessage()]);
        }
    }
}
