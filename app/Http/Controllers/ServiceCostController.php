<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use App\Helpers\CommonHelper;

class ServiceCostController extends Controller
{

    private $cashfreePayinUrl;
    private $cashfreeappid;
    private $cashfreesecretkey;
    private $cashfreeapiversion;

    public function __construct()
    {
        $this->cashfreePayinUrl = config('payin.cashfree_url');
        $this->cashfreeappid = config('payin.cashfree_app_id');
        $this->cashfreesecretkey = config('payin.cashfree_secret_key');
        $this->cashfreeapiversion = config('payin.cashfree_api_version');
    }

    public function getServiceCost(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'amount' => 'required|integer|min:1',
            'gst' => 'required|integer',
            'total' => 'required|integer',
        ]);

        $user = Auth::user();
        dd($user);
        $userId = Auth::id();
        dd($userId);
        $name = $user->name;
        $mobile = $user->mobile;
        $email = $user->email;
        dd($userId ,$name,$mobile,$email);

        $txnId = CommonHelper::generateTransactionId();

        $getProviderSlug = CommonHelper::getProviderSlug($userId, 'PAYIN_SERVICE_ID');

        if (!$getProviderSlug) {
            return response()->json([
                'message' => 'Provider not found for the user and service'
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
                    "link_amount" => $request->total_amount,
                    "link_currency" => "INR",
                    "link_id" => $request->$txnId,
                    "link_purpose" => "Payment"
                ];

                // dd($this->cashfreeapiversion, $this->cashfreeappid, $this->cashfreesecretkey, $payload);

                $response = Http::withHeaders([
                    'x-api-version' => $this->cashfreeapiversion,
                    'x-client-id' => $this->cashfreeappid,
                    'x-client-secret' => $this->cashfreesecretkey,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(40)
                    ->connectTimeout(30)
                    ->retry(
                        3,
                        2000,
                        function ($exception, $request) {
                            return $exception instanceof ConnectionException;
                        }
                    )
                    ->post($url, $payload);

                $result = $response->json();
                dd($result);

                Log::info('Cashfree Payin Response', [
                    'request' => $payload,
                    'response' => $result,
                ]);

                return response()->json([
                    'data' => $result,
                    'status' => $response->successful(),
                    'message' => $response->successful() ? 'Payment initiated successfully' : 'API Error',
                ]);

                break;
        }
    }
}
