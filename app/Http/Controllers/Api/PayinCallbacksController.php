<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Helpers\TransactionHelper;

class PayinCallbacksController extends Controller
{
    private $payinServiceSlug;

    public function __construct()
    {
        $this->payinServiceSlug = config('constants.PAYIN_SERVICE_SLUG');
    }

    public function callbacks(Request $request, $type)
    {
        switch ($type) {
            case 'cgpey':
                Log::info('CGPEY callback received:', $request->all());

                try {
                    $data = $request->all();

                    if (empty($data)) {
                        return response()->json([
                            'message' => 'No data received in request',
                            'status'  => false,
                        ], 400);
                    }

                    if (empty($data['order_id'])) {
                        Log::error('CGPEY callback missing order_id');
                        return response()->json(['success' => false, 'message' => 'Missing order_id'], 400);
                    }

                    $updateData = [
                        'status'   => $data['status'] ?? 'pending',
                        'utr'      => $data['utr'] ?? null,
                        'order_id' => $data['order_id'] ?? null,
                    ];

                    $updated = DB::table('upi_collections')
                        ->where('client_txn_id', $data['order_id'])
                        ->update($updateData);

                    if (!$updated) {
                        Log::warning("No record found for order_id: {$data['order_id']}");
                    }

                    DB::table('upi_callbacks')->insert([
                        'txn_id'          => '',
                        'txn_order_id'    => $data['order_id'] ?? null,
                        'amount'          => $data['amount'],
                        'utr'             => $data['utr'],
                        'root'            => '',
                        'message'         => $data['message'],
                        'response'        => '',
                        'status'          => $data['status'],
                        'updated_by '     => '',
                    ]);


                    $useridRow = DB::table('kavach_payins')
                        ->where('client_txn_id', $data['order_id'])
                        ->first(['user_id']);

                    if (! $useridRow) {
                        Log::error("No user found for order_id: {$data['order_id']}");
                        return response()->json(['success' => false, 'message' => 'User not found'], 404);
                    }

                    $user = DB::table('webhooks')->where('user_id', $useridRow->user_id)->first();
                    if (empty($user) || empty($user->payin_webhook_url)) {
                        Log::error("User or webhook URL not found for user_id: {$useridRow->user_id}");
                        return response()->json(['success' => false, 'message' => 'Webhook URL not found'], 404);
                    }

                    $callbackUrl = $user->payin_webhook_url;

                    $payload = [
                        'status'          => $data['status'] ?? 'pending',
                        'order_id'        => $data['order_id'] ?? '',
                        'utr'             => $data['utr'] ?? '',
                        'customer_mobile' => $data['customer_mobile'] ?? '',
                        'amount'          => $data['amount'] ?? '',
                        'date'            => $data['updated_at'] ?? now()->toDateTimeString(),
                    ];

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->retry(2, 200)->post($callbackUrl, $payload);

                    if ($response->successful()) {
                        Log::info('Callback sent successfully', [
                            'order_id' => $data['order_id'],
                            'response' => $response->body(),
                        ]);
                    } else {
                        Log::error('Callback failed', [
                            'order_id' => $data['order_id'],
                            'status'   => $response->status(),
                            'body'     => $response->body(),
                        ]);
                    }

                    return response()->json([
                        'success'      => true,
                        'message'      => 'Callback processed and sent successfully',
                        'callback_url' => $callbackUrl,
                        'payload'      => $payload,
                    ]);
                } catch (\Exception $e) {
                    Log::error('CGPEY callback error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Server error: ' . $e->getMessage(),
                    ], 500);
                }

                break;

            case 'cashfree':

                Log::info('Cashfree callback received', $request->all());

                try {

                    $data = $request->all();

                    if (empty($data) || empty($data['order_id'])) {
                        Log::error('Invalid Cashfree callback data', $data);

                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid data or missing order_id'
                        ], 400);
                    }

                    $orderId = $data['order_id'];

                    // Fetch record first
                    $collection = DB::table('upi_collections')
                        ->where('client_txn_id', $orderId)
                        ->first();

                    if (! $collection) {
                        Log::warning("No record found for order_id: {$orderId}");

                        return response()->json([
                            'success' => false,
                            'message' => 'Order not found'
                        ], 404);
                    }

                    // Normalize status
                    $status = strtolower($data['status'] ?? 'pending');

                    if (! in_array($status, ['success', 'failed', 'pending'])) {
                        $status = 'pending';
                    }

                    // Update main table
                    DB::table('upi_collections')
                        ->where('client_txn_id', $orderId)
                        ->update([
                            'status'   => $status,
                            'utr'      => $data['utr'] ?? null,
                            'order_id' => $orderId,
                            'type'     => $type,
                            'updated_at' => now()
                        ]);

                    // Store callback log
                    DB::table('upi_callbacks')->insert([
                        'txn_id'       => '',
                        'txn_order_id' => $orderId,
                        'amount'       => $data['amount'] ?? 0,
                        'utr'          => $data['utr'] ?? null,
                        'root'         => '',
                        'message'      => $data['message'] ?? null,
                        'response'     => json_encode($data),
                        'status'       => $status,
                        'updated_by'   => '',
                        'created_at'   => now(),
                    ]);

                    // Prepare payload
                    $payload = [
                        'status'          => $status,
                        'order_id'        => $orderId,
                        'utr'             => $data['utr'] ?? '',
                        'customer_mobile' => $data['customer_mobile'] ?? '',
                        'amount'          => $data['amount'] ?? 0,
                        'date'            => now()->toDateTimeString(),
                    ];

                    //  Send callback to client
                    $sendCallback = TransactionHelper::sendPayinCallback(
                        $collection->user_id,
                        $orderId,
                        $payload,
                        $this->payinServiceSlug
                    );

                    return response()->json([
                        'success' => true,
                        'message' => 'Callback received successfully',
                        'callback_status' => $sendCallback
                    ]);
                } catch (\Exception $e) {

                    Log::error('Cashfree callback error', [
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Server error'
                    ], 500);
                }

                break;
            case 'easebuzz':
                Log::info('Easebuzz callback received', $request->all());
                try {
                    $data = $request->all();

                    if (empty($data) || empty($data['txnid'])) {
                        Log::error('Invalid Easebuzz callback data', $data);

                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid data or missing txnidtxnid'
                        ], 400);
                    }

                    $txnId = $data['txnid'];

                    // Fetch record first
                    $collection = DB::table('seamless_upi_collections')
                        ->where('cust_txn_id', $txnId)
                        ->first();

                    if (! $collection) {
                        Log::warning("No record found for txnid: {$txnId}");

                        return response()->json([
                            'success' => false,
                            'message' => 'txn not found'
                        ], 404);
                    }

                    // Normalize status
                    $status = strtolower($data['status'] ?? 'pending');

                    if (! in_array($status, ['success', 'failed', 'pending'])) {
                        $status = 'pending';
                    }

                    // Update main table
                    DB::table('seamless_upi_collections')
                        ->where('cust_txn_id', $txnId)
                        ->update([
                            'status'   => $status,
                            'utr'      => $data['bank_ref_num'] ?? null,
                            'txn_order_id' => $data['easepayid'] ?? null,
                            'route'     => $type,
                            'updated_at' => now()
                        ]);

                    // Store callback log
                    DB::table('upi_callbacks')->insert([
                        'txn_id'       => $txnId,
                        'txn_order_id' => $data['easepayid'] ?? null,
                        'amount'       => $data['amount'] ?? 0,
                        'utr'          => $data['bank_ref_num'] ?? null,
                        'root'         => $type,
                        'message'      => $data['error_Message'] ?? $data['error'] ?? 'Payment callback received',
                        'response'     => json_encode($data),
                        'status'       => $status,
                        'updated_by'   => now(),
                        'created_at'   => now(),
                    ]);

                    // Prepare payload
                    $payload = [
                        'status'          => $status,
                        'client_txn_id'   => $txnId,
                        'order_id'        => $data['easepayid'] ?? '',
                        'utr'             => $data['bank_ref_num'] ?? '',
                        'amount'          => $data['amount'] ?? 0,
                        'date'            => now()->toDateTimeString(),
                    ];

                    //  Send callback to client
                    $sendCallback = TransactionHelper::sendPayinCallback(
                        $collection->user_id,
                        $txnId,
                        $payload,
                        $this->payinServiceSlug
                    );

                    if ($sendCallback['status'] ?? false) {

                        DB::table('seamless_upi_collections')
                            ->where('cust_txn_id', $txnId)
                            ->update([
                                'is_webhook_send' => 1,
                                'webhook_send_at' => now()->toDateTimeString()
                            ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Callback received successfully',
                        'callback_status' => $sendCallback
                    ]);
                } catch (\Exception $e) {
                    Log::error('Easebuzz callback error', [
                        'error' => $e->getMessage(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Server error'
                    ], 500);
                }
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid callback type',
                ], 400);
        }
    }
}
