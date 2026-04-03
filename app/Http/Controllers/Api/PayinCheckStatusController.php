<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayinCheckStatusController extends Controller
{
    public function checkStatus($clientTxnId = null)
    {
        //   dd($clientTxnId);
        $type = DB::table('kavach_payins')->where('client_txn_id', $clientTxnId)->first();
        // dd($type->type);
        switch ($type->type) {
            case 'cgpey':
                try {

                    $url =  $this->cgpeyCheckStatusUrl;

                    $payload = [
                        'transaction_id' => $clientTxnId ?? NULL,
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
