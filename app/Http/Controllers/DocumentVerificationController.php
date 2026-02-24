<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class DocumentVerificationController extends Controller
{
    private $clientID ;
    private $clientSecret;
    private $apiVersion;

    public function __construct(){
        $this->clientID = 'CF397795D6E21N84UCVC73BRNP00';
        $this->clientSecret = 'cfsk_ma_prod_459c9e17775fbc453852a947d9e79d7c_897e28f8';
        $this->apiVersion = 'V2';

    }


    

    protected function getAuthToken(Request $request){
        try{

            $response = Http::withHeaders([
                'x-api-version' => '',
                'x-partner-api-key' => $this->clientID,
                'x-partner-merchantid' => '',

            ])->get('https://sandbox.cashfree.com/gc/authorize', [
                
            ]);

            return $response['data']->token;
        }catch(Exception $e){
            return response()->json([
                'status'=> false,
                'message'=> $e->getMessage()
            ]);
        }
    }
    public function VerifyAccountDetails(Request $request){
        try{
            $request->validate([
                'name'=> 'required|string',
                'bankAccount'=> 'required|string',
                'ifsc'=> 'required|string',
                'phone'=> 'required|string',
                'remarks'=> 'required|string'

            ]);
            $token = $this->getAuthToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get('https://payout-api.cashfree.com/payout/v1.2/validation/bankDetails', [
                'bankAccount' => $request->bankAccount,
                'ifsc' => $request->ifsc,
                'name' => $request->name,
            ]);

            return $response->json();

        }catch(\Exception $e){
            return response()->json([
                'status'=> false,
                'messgae'=> $e->getMessage()
            ]);
        }
    }

    public function verifyCinNumber(Request $request){
        try{
            $request->validate([
                'cin'=> 'required|string'
            ]);

            $verificationId = 'CIN'.time();

            $response = Http::withHeaders([
               
                'Content-Type' => 'application/json',
                'x-client-id' => '',
                'x-client-secret'=> ''
            ])->get('https://sandbox.cashfree.com/verification/cin', [
                'verification_id' => $verificationId,
                'cin' => $request->cin,
                
            ]);


            return response()->json([
                'status'=> true,
                'data'=> $response
            ]);



        }catch(Exception $e){
             return response()->json([
                'status'=> false,
                'messgae'=> $e->getMessage()
            ]);
        }
    }

    public function verifyGstinNumber(Request $request){
        try{
            $request->validate([
                'GSTIN'=> 'required|string'
            ]);

            $verificationId = 'CIN'.time();

            $response = Http::withHeaders([
               
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret'=> $this->clientSecret
            ])->post('https://sandbox.cashfree.com/verification/gstin', [
                "GSTIN"=> $request->GSTIN,
                
            ]);


            return response()->json([
                'status'=> true,
                'data'=> $response
            ]);



        }catch(Exception $e){
             return response()->json([
                'status'=> false,
                'messgae'=> $e->getMessage()
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

            $endpoint = "https://sandbox.cashfree.com/verification/pan";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => "3336833f056e54e01f0f45142b386333",
                'x-client-secret' => "2dbf0e7e63bc1f6b084faddf33ce0b5dff4a5cd3",
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
