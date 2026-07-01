<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DocumentVerification extends Controller
{
    private $cashfree_clientId, $cashfree_clientSecret, $bankAccount_Url, $ifsc_Url, $pan_Url, $gstin_Url, $cin_Url, $aadharMasking_Url;
    public function __construct()
    {
        $this->cashfree_clientId = config('verification.client_id');
        $this->cashfree_clientSecret = config('verification.client_secret');
        $this->bankAccount_Url = config('verification.bankAccount_url');
        $this->ifsc_Url = config('verification.ifsc_url');
        $this->pan_Url = config('verification.pan_url');
        $this->gstin_Url = config('verification.gstin_url');
        $this->cin_Url = config('verification.cin_url');
        $this->aadharMasking_Url = config('verification.aadharMasking_url');
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

    public function ifscVerify(Request $request)
    {

        $validatedData = $request->validate([
            'verification_id' => 'required|string',
            'ifsc' => 'required|string',
        ]);

        $type = 'cashfree';

        switch ($type) {
            case 'cashfree':
                $payload = [
                    'verification_id' => $request->verification_id,
                    'ifsc' => $request->ifsc,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->ifsc_Url, $payload);

                $result = $response->json();
                dd($result);
                return 'hello';
                break;
            default:
                return response()->json([
                    'message' => 'Provider not supported'
                ]);
        }
    }

    public function panVerify(Request $request)
    {

        $validationData = $request->validate([
            'pan' => 'required|string',
            'name' => 'nullable|string',
        ]);

        $type = 'cashfree';

        switch ($type) {
            case 'cashfree':
                $payload = [
                    'pan' => $request->pan,
                    'name' => $request->name,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->pan_Url, $payload);

                $result = $response->json();
                dd($result);
                break;

            default:
                return response()->json(['message' => 'Provider not supported'], 400);
        }
    }


    public function gstinVerify(Request $request)
    {
        $validationData = $request->validate([
            'GSTIN' => 'required|string',
            'business_name' => 'nullable|string',
        ]);

        $type = 'cashfree';

        switch ($type) {
            case 'cashfree':
                $payload = [
                    'GSTIN' => $request->GSTIN,
                    'business_name' => $request->business_name,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->gstin_Url, $payload);

                $result = $response->json();
                dd($result);
                break;

            default:
                return response()->json(['message' => 'Provider not supported'], 400);
        }
    }

    public function cinVerify(Request $request)
    {
        $validationData = $request->validate([
            'verification_id' => '',
            'CIN' => 'required|string',
            'company_name' => 'nullable|string',
        ]);

        $type = 'cashfree';

        switch ($type) {
            case 'cashfree':
                $payload = [
                    'CIN' => $request->CIN,
                    'company_name' => $request->company_name,
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->cin_Url, $payload);

                $result = $response->json();
                dd($result);
                break;

            default:
                return response()->json(['message' => 'Provider not supported'], 400);
        }
    }


    public function aadharMaskingVerify(Request $request)
    {
        $validationData = $request->validate([
            'verification_id' => 'required|string',
            'aadhar_image' => 'required|string',
        ]);

        $type = 'cashfree';

        switch ($type) {
            case 'cashfree':
                $payload = [
                    'verification_id' => $request->verification_id,
                    'image' => $request->aadhar_image,
                ];

                // Assuming there's an endpoint for Aadhar masking in Cashfree
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-client-id' => $this->cashfree_clientId,
                    'x-client-secret' => $this->cashfree_clientSecret,
                ])->post($this->aadharMasking_Url, $payload);

                $result = $response->json();
                dd($result);
                break;

            default:
                return response()->json(['message' => 'Provider not supported'], 400);
        }
    }
}
