<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use App\Helpers\CommonHelper;
use App\Models\UpiCollection;


class ServiceCostController extends Controller
{

    private $cashfreePayinUrl;
    private $cashfreeappid;
    private $cashfreesecretkey;
    private $cashfreeapiversion;
    private $PAYIN_SERVICE_ID;

    public function __construct()
    {
        $this->cashfreePayinUrl = config('payin.cashfree_url');
        $this->cashfreeappid = config('payin.cashfree_app_id');
        $this->cashfreesecretkey = config('payin.cashfree_secret_key');
        $this->cashfreeapiversion = config('payin.cashfree_api_version');
        $this->PAYIN_SERVICE_ID = config('constants.PAYIN_SERVICE_ID');
    }

    public function getServiceCost(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:100',
            'gst' => 'required|integer',
            'total' => 'required|integer',
        ]);

        $user = Auth::user();

        $userId = $user->id;
        $name = $user->name;
        $mobile = $user->mobile;
        $email = $user->email;

        $txnId = CommonHelper::generateTransactionId();
        $ConnectpeOrderId = CommonHelper::generateConnectPeTransactionId();

        $getProviderSlug = CommonHelper::getProviderSlug($userId, $this->PAYIN_SERVICE_ID);

        if (!$getProviderSlug['status']) {
            return response()->json([
                'message' => $getProviderSlug['message']
            ]);
        }

        $providerSlug = $getProviderSlug['provider_slug'] ?? null;

        switch ($providerSlug) {

            case 'cashfree':

                $url = $this->cashfreePayinUrl;
                // dd($url);

                $payload = [
                    "customer_details" => [
                        "customer_name" => $name,
                        "customer_email" => $email,
                        "customer_phone" => $mobile
                    ],
                    "link_amount" => $request->total,
                    "link_currency" => "INR",
                    "link_id" => $txnId,
                    "link_purpose" => "Payment"
                ];

                // dd($this->cashfreeapiversion, $this->cashfreeappid, $this->cashfreesecretkey, $payload);

                $response = Http::withHeaders([
                    'x-api-version' => $this->cashfreeapiversion,
                    'x-client-id' => $this->cashfreeappid,
                    'x-client-secret' => $this->cashfreesecretkey,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(30)
                    ->post($url, $payload);

                $result = $response->json();
                dd($result);

                Log::info('Cashfree Payin Response', [
                    'request' => $payload,
                    'response' => $result,
                ]);

                if ($result->successful()) {

                    $upiUrl = $result['data']['link_url'];

                    parse_str(parse_url($upiUrl, PHP_URL_QUERY), $params);
                    $pa = $params['pa'] ?? null;

                    UpiCollection::create([
                        'user_id' => $userId,
                        'cust_name' => $name,
                        'cust_mobile' => $mobile,
                        'cust_email' => $email,
                        'cust_txn_id' => $txnId,
                        'connectpe_order_id ' => $ConnectpeOrderId,
                        'amount' => $request->amount,
                        'tax' => $request->gst,
                        'net_amount' => $request->total,
                        'qr_intent' => $upiUrl,
                        'txn_id' => $result['data']['txnId'],
                        'txn_order_id' => $orderID,
                        'type'  => 'setup_cost',
                        'root' => $pa,
                        'status' => 'initiated',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return response()->json([
                        'status' => true,
                        'data' => $result,
                        'message' => $response->successful() ? 'Payment initiated successfully' : 'API Error',
                    ]);
                } else {
                    return response([
                        'status' => false,
                        'message' => 'Cashfree api Error'
                    ]);
                }

                break;

            default:
                return response()->json([
                    'status' => true,
                    'message' => 'Invalid payin type.'
                ], 400);
        }
    }
}
