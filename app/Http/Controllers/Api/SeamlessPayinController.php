<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\SeamlessPayinHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SeamlessPayinController extends Controller
{
    public function upiDeepLink(Request $request)
    {
        $request->validate([
                    'name' => 'required|string|max:100',
                    'email' => 'required|email|max:100',
                    'mobile_number' => 'required|digits:10',
                    'amount' => 'required|numeric|min:1',
                    'transaction_id' => 'required|string|max:25',
        ]);




        switch ('easebuzz') {
            case 'easebuzz':
                try {
                    // GENERATE ACCESS KEY INTERNALLY

                    $accessKeyResponse = SeamlessPayinHelper::generateEasebuzzAccessKey([

                        'amount'         => $request->amount,
                        'firstname'      => $request->name,
                        'phone'          => $request->mobile_number,
                        'email'          => $request->email,
                        'transaction_id' => $request->transaction_id ?? null,
                    ]);

                    // CHECK ACCESS KEY

                    if (
                        !($accessKeyResponse['status'] ?? false) ||
                        empty($accessKeyResponse['access_key'] ?? null)
                    ) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Unable to generate Easebuzz access key',
                            'response' => $accessKeyResponse,
                        ], 400);
                    }


                    $accessKey = $accessKeyResponse['access_key'] ?? null;

                    // GENERATE UPI DEEPLINK

                    $data = [
                        'access_key'   => $accessKey,
                        'payment_mode' => 'UPI',
                        'upi_qr'       => 'true',
                        'request_mode' => 'SUVA',
                    ];

                    $url = 'https://pay.easebuzz.in/initiate_seamless_payment/';


                    $response = Http::asForm()
                        ->acceptJson()
                        ->post($url, $data);


                    $result = $response->json();

                    if (
                        $response->successful() &&
                        ($result['status'] ?? false) === true
                    ) {

                        return response()->json([
                            'status'  => true,
                            'message' => $result['msg_desc']
                                ?? 'UPI deeplink generated successfully',

                            'qr_link' => $result['qr_link'] ?? null,

                            'transaction_id' =>
                            $accessKeyResponse['transaction_id'] ?? null,
                        ]);
                    }

                    // EASEBUZZ ERROR

                    return response()->json([
                        'status'   => false,
                        'message'  => $result['msg_desc']
                            ?? 'Unable to generate UPI deeplink',

                        'response' => $result,
                    ], 400);
                } catch (\Exception $e) {

                    return response()->json([
                        'status'  => false,
                        'message' => 'Easebuzz API error',
                        'error'   => $e->getMessage(),
                    ], 500);
                }
                break;
            default:
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid request type',
                ], 400);
        }
    }
}
