<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DocumentVerification extends Controller
{
    private $cashfree_clientId, $cashfree_clientSecret, $bankAccount_Url;
    public function __construct()
    {
        $this->cashfree_clientId = config('verification.client_id');
        $this->cashfree_clientSecret = config('verification.client_secret');
        $this->bankAccount_Url = config('verification.bankAccount_url');
    }

    public function bankAccountVerify(Request $request)
    {
        $validatedData = $request->validate([
            'bank_account' => 'required|string',
            'ifsc'         => 'required|string',
            'name'         => 'nullable|string',
            'phone'        => 'nullable|string',
        ]);

        $type = 'cashfree';
        
        switch ($type) {
            case 'cashfree':

                $payload = [
                    'bank_account' => $request->bank_account,
                    'ifsc' => $request->ifsc,
                    'name' => $request->name,
                    'phone' => $request->phone,
                ];

                // dd($payload);
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->bankAccount_Url, $payload);

                dd($response->json());
                return 'hello';
                break;

            default:
                return response()->json(['message' => 'Provider not supported'], 400);
        }
    }
}
