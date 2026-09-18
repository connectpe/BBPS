<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayinCheckStatusController extends Controller
{
    public function checkStatus($custTxnId = null)
    {
        //   dd($clientTxnId);
        $type = DB::table('seamless_upi_collections')->where('cust_txn_id', $custTxnId)->first();
        $userid = $type->user_id;
        // dd($type->type);
        switch ($type->route) {
            case 'cgpey':
                try {

                    $url =  $this->cgpeyCheckStatusUrl;

                    $payload = [
                        'transaction_id' => $custTxnId ?? NULL,
                    ];

                    $header = [
                        'ip-address' => $this->ip,
                        'x-secret-key' => $this->secretkey,
                        'x-api-key' => $this->apikey,
                    ];



                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($url, $payload);



                    $result = $response->json();

                    // dd($result);


                    if ($response->failed()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Failed to connect to CGPEY API',
                            'response' => $result
                        ], 500);
                    }



                    if (isset($result['status'])) {

                        DB::table('kavach_payins')
                            ->where('client_txn_id', $payload['transaction_id'])
                            ->update([
                                'status'     => $result['status'],
                                'utr'        => $result['utr'] ?? null,
                                'updated_at' => now(),
                            ]);
                    }


                    return response()->json($result, $response->status());
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                }
                break;

            case 'easebuzz':
                try {

                    $transaction = DB::table('seamless_upi_collections')
                        ->where('cust_txn_id', $custTxnId)
                        ->first();

                    if (!$transaction) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Transaction not found',
                        ], 404);
                    }

                    if (in_array(strtolower($transaction->status), ['success', 'failed'])) {

                        return response()->json([
                            'status' => true,
                            'message' => 'Transaction status already finalized',
                            'data' => [
                                'transaction_id' => $transaction->cust_txn_id,
                                'order_id'       => $transaction->connectpe_order_id,
                                'amount'         => $transaction->amount,
                                'status'         => $transaction->status,
                                'utr'            => $transaction->utr,
                            ],
                        ]);
                    }

                    $oauthUser = DB::table('oauth_users')->where('user_id', $userid)->first();

                    $key  = $oauthUser->client_id == '31ZZC7TRU' ? 'ZBCLPSC4KY' : $oauthUser->client_id;
                    $salt = $oauthUser->client_secret == 'SUO1H846U' ? 'QZBZ8QDCP1' : $oauthUser->client_secret;

                    $url = 'https://dashboard.easebuzz.in/transaction/v2.1/retrieve';

                    $txnid = $custTxnId;

                    // Hash sequence:
                    // key|txnid|salt
                    $hashString = $key . '|' . $txnid . '|' . $salt;

                    $hash = hash('sha512', $hashString);

                    Log::info('Easebuzz Check Status Request', [
                        'txnid' => $txnid,
                        'hash_string' => $hashString,
                        'url' => $url,
                    ]);

                    // Send request
                    $response = Http::asForm()
                        ->acceptJson()
                        ->post($url, [
                            'key'   => $key,
                            'txnid' => $txnid,
                            'hash'  => $hash,
                        ]);

                    $result = $response->json();

                    Log::info('Easebuzz Check Status Response', [
                        'txnid' => $txnid,
                        'http_status' => $response->status(),
                        'response' => $result,
                    ]);

                    if (!$response->successful()) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Easebuzz API request failed',
                            'data' => $result,
                        ], $response->status());
                    }

                    DB::table('seamless_upi_collections')
                        ->where('cust_txn_id', $custTxnId)
                        ->update([
                            'status' => $result['msg']['status'] ?? null,
                            'utr'    => $result['msg']['bank_ref_num'] ?? null,
                        ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Transaction status fetched successfully',
                        'data' => [
                            'transaction_id' => $custTxnId,
                            'order_id' => $result['msg']['order_id'] ?? null,
                            'amount' => $result['msg']['amount'] ?? null,
                            'status' => $result['msg']['status'] ?? null,
                            'utr'    => $result['msg']['bank_ref_num'] ?? null,
                        ],
                    ]);
                } catch (\Exception $e) {

                    Log::error('Easebuzz Check Status Error', [
                        'txnid' => $custTxnId,
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ], 500);
                }
                break;

            case 'spiralpay':
                try {

                    $token = \App\Helpers\SpiralPayHelper::getToken();

                    $url = $this->spiralPay_checkStatusUrl . '/' . $clientTxnId;

                    $response = Http::withToken($token)->get($url);
                    // dd($response);
                    if ($response->successful()) {

                        $data = $response->json();
                        // dd($data);
                        if (isset($data['data'])) {
                            $paymentData = $data['data'];
                            $status = $paymentData['status']; //'Success' or 'Failed'

                            DB::table('kavach_payins')
                                ->where('client_txn_id', $clientTxnId)
                                ->update([
                                    'status' => strtoupper($status),
                                    'utr'    => $paymentData['utr'] ?? null,
                                ]);

                            return response()->json([
                                'status'  => true,
                                'message' => 'Status Updated Successfully!',
                                'current_status' => $status
                            ]);
                        }
                    }

                    return response()->json([
                        'status'  => false,
                        'message' => 'Check Status Api Error: ' . ($response->body() ?? 'No response'),
                    ], 400);
                } catch (\Exception $e) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Internal Server Error: ' . $e->getMessage(),
                    ], 500);
                }
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unsupported type.',
                ], 400);
        }
    }
}
