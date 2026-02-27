<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use App\Models\UsersBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DocumentVerificationController extends Controller
{
    private $clientID;
    private $clientSecret;
    private $apiVersion;
    private $userId;
    private $user;

    public function __construct()
    {
        $this->clientID = env('CASHFREE_CLIENT_ID');
        $this->clientSecret = env('CASHFREE_CLIENT_SECRET');
        $this->apiVersion = 'V2';
        $this->userId = Auth::id();
        $this->user = Auth::user();
    }

    public function getDocumentData()
    {
        try {

            $user = Auth::user();
            // dd($user);
            $businessInfo = $user->business;

            $businessInfo = BusinessInfo::select('address', 'business_pan_number', 'business_pan_name', 'is_pan_verify', 'gst_number', 'is_gstin_verify', 'cin_no', 'is_cin_verify', 'is_bank_details_verify')->where('user_id', $this->userId)->first();


            if (!$businessInfo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business information not found for the user.'
                ]);
            }

            $usersBank = UsersBank::select('account_number', 'ifsc_code', 'benificiary_name')->where('user_id', $this->userId)->first();

            if (!$usersBank) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bank details not found for the user.'
                ]);
            }

            return response()->json([
                'status' => true,

                'name' => $this->user->name ?? '-',
                'email' => $this->user->email ?? '-',
                'phone' => $this->user->mobile ?? '-',
                'address' => $businessInfo->address ?? '-',
                'videokyc_verified' => 1,

                'business_pan_number' => $businessInfo->business_pan_number ?? '-',
                'business_pan_name' => $businessInfo->business_pan_name ?? '-',
                'pan_verified' => $businessInfo->is_pan_verify ?? 0,

                'gst_number' => $businessInfo->gst_number ?? '-',
                'is_gstin_verify' => $businessInfo->is_gstin_verify ?? 0,

                'cin_no' => $businessInfo->cin_no ?? '-',
                'is_cin_verify' => $businessInfo->is_cin_verify ?? 0,

                'account_number' => $usersBank->account_number ?? '-',
                'ifsc_code' => $usersBank->ifsc_code ?? '-',
                'benificiary_name' => $usersBank->benificiary_name ?? '-',
                'bank_verified' => $businessInfo->is_bank_details_verify ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function getAuthToken(Request $request)
    {
        try {

            $response = Http::withHeaders([
                'x-api-version' => $this->apiVersion,
                'x-partner-api-key' => $this->clientID,
                'x-partner-merchantid' => '',


            ])->get('https://api.cashfree.com/gc/authorize', []);


            return $response['data']->token;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function VerifyAccountDetails(Request $request)
    {
        dd($request->all());
        try {
            $request->validate([
                'name' => 'required|string',
                'bankAccount' => 'required|string',
                'ifsc' => 'required|string',
                'phone' => 'required|string',
                'remarks' => 'required|string'

            ]);
            $token = $this->getAuthToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get('https://api.cashfree.com/payout/v1.2/validation/bankDetails', [
                'bankAccount' => $request->bankAccount,
                'ifsc' => $request->ifsc,
                'name' => $request->name,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messgae' => $e->getMessage()
            ]);
        }
    }

    public function verifyCinNumber(Request $request)
    {
        try {
            $request->validate([
                'cin_no' => 'required|string'
            ]);

            $verificationId = 'CIN' . time();

            $response = Http::withHeaders([

                'Content-Type' => 'application/json',

                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret
            ])->post('https://api.cashfree.com/verification/cin', [
                'verification_id' => $verificationId,
                'cin' => $request->cin_no,

            ]);

            dd($response->json());

            if ($response['status'] == true && $response['data']['status'] == 'VALID' && $response['data']['cin_status'] == 'ACTIVE') {
                BusinessInfo::where('user_id', $this->userId)->update([
                    'is_cin_verify' => '1',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'CIN verification completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function verifyGstinNumber(Request $request)
    {
        try {
            $request->validate([
                'gst_number' => 'required|string'
            ]);

            $response = Http::withHeaders([

                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,

            ])->post('https://api.cashfree.com/verification/gstin', [
                "GSTIN" => $request->gst_number,
            ]);

            dd($response->json());

            if ($response['status'] == true && $response['data']['valid'] == true) {
                BusinessInfo::where('user_id', $this->userId)->update([
                    'is_gstin_verify' => '1',
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $response['data']['message'] ?? 'GSTIN verification completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function panVerify(Request $request)
    {
        try {
            $request->validate([
                'pan_number'  => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                'pan_name' => 'required|string',
            ]);

            $payload = [
                'pan'  => $request->pan_number,
                'name' => $request->pan_name,
            ];

            $endpoint = "https://api.cashfree.com/verification/pan";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post($endpoint, $payload);

            dd($response->json());

            if ($response['status'] == true && $response['data']['valid'] == true) {
                BusinessInfo::where('user_id', $this->userId)->update([
                    'is_pan_verify' => '1',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => $response['data']['message'] ?? 'PAN verification completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function verifyIfsc(Request $request)
    {
        try {
            $request->validate([

                'ifsc' => 'required|string',
            ]);

            $verificationId = 'IFSC' . time();

            $payload = [
                'ifsc'  => $request->ifsc,
                'verification_id' => $verificationId,
            ];
            // dd($payload);
            $endpoint = "https://api.cashfree.com/verification/ifsc";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post($endpoint, $payload);
            // dd($response);
            return response()->json([
                'status' => true,
                'data' => $response->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function createUserToCashFree(Request $request)
    {
        try {

            $user_id = 'USER' . $this->userId;
            $date = '2024-12-01';
            $payload = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'user_id' => $user_id
            ];
            $endpoint = 'https://api.cashfree.com/verification/user';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
                'x-api-version' => $date
            ])->post($endpoint, $payload);

            $finalResponse = $response->json();
            $data = [];
            if ($finalResponse['code'] == 'user_id_already_exists') {
                $data = [
                    'user_id' => $user_id,

                ];
            } else {
                $data = [
                    'user_id' => $finalResponse['user_id'],
                    'user_reference_id' => $finalResponse['user_reference_id'] ?? null,

                ];
            }

            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function initiateVideoKyc(Request $request)
    {
        try {
            // dd($request->all());
            $request->validate([
                'phone' => 'required',
                'email' => 'required',
                'name' => 'required',
                'address' => 'required'
            ]);

            $date = '2024-12-01';

            $response = $this->createUserToCashFree($request);

            // dd($response['user_id']);
            $referenceId = $response['user_reference_id'] ?? null;

            $payload = [
                'verification_id' => time() . round(10000, 99999),
                'user_template' => 'vkyc_user_template_v1',
                'user_id' => $response['user_id'],
                'notification_types' => ['whatsapp'],
                'user_reference_id' => $referenceId
            ];
            // dd(1);
            $endpoint = "https://api.cashfree.com//verification/vkyc";
            $apiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
                'x-api-version' => $date
            ])->post($endpoint, $payload);
            dd($apiResponse->json());
            return response()->json([
                'status' => true,
                'message' => 'Kyc link genratewd successfully',
                'data' => $apiResponse->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
