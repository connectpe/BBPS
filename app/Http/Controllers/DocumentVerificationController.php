<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;


class DocumentVerificationController extends Controller
{
    private $clientID;
    private $clientSecret;
    private $apiVersion;


   

    public function __construct(){
        $this->clientID = env('CASHFREE_CLIENT_ID');
        $this->clientSecret = env('CASHFREE_CLIENT_SECRET');
        $this->apiVersion = 'V2';
    }

    public function getDocumentData()
    {
        $user = Auth::user();

        $businessInfo = $user->businessInfo;
        $usersBank = $user->bankDetail;

        return response()->json([
            'status' => true,
            'pan_number' => $businessInfo->pan_number ?? '-',
            'pan_verified' => $businessInfo->pan_verified ?? 0,

            'gst_number' => $businessInfo->gst_number ?? '-',
            'gst_verified' => $businessInfo->gst_verified ?? 0,

            'cin_no' => $businessInfo->cin_no ?? '-',
            'cin_verified' => $businessInfo->cin_verified ?? 0,

            'account_number' => $usersBank->account_number ?? '-',
            'bank_verified' => $usersBank->bank_verified ?? 0,
        ]);
    }

    protected function getAuthToken(Request $request)
    {
        try {

            $response = Http::withHeaders([
                'x-api-version' => $this->apiVersion,
                'x-partner-api-key' => $this->clientID,
                'x-partner-merchantid' => '',


            ])->get('https://api.cashfree.com/gc/authorize', [
                
            ]);


            return $response['data']->token;
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function VerifyAccountDetails(Request $request)
    {
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
                'cin' => 'required|string'
            ]);

            $verificationId = 'CIN' . time();

            $response = Http::withHeaders([

                'Content-Type' => 'application/json',

                'x-client-id' => $this->clientID,
                'x-client-secret'=> $this->clientSecret
            ])->post('https://api.cashfree.com/verification/cin', [
                'verification_id' => $verificationId,
                'cin' => $request->cin,

            ]);
            // dd($response->json());


            return response()->json([

                'status'=> true,
                'data'=> $response->json()
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
                'GSTIN' => 'required|string'
            ]);
           
            $response = Http::withHeaders([

                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
               
            ])->post('https://api.cashfree.com/verification/gstin', [
                "GSTIN"=> $request->GSTIN,
            ]);
          
            

            return response()->json([

                'status'=> true,
                'data'=> $response->json()
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
                'pan'  => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
                'name' => 'required|string',
            ]);

            $payload = [
                'pan'  => $request->pan,
                'name' => $request->name,
            ];

            $endpoint = "https://api.cashfree.com/verification/pan";

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
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
