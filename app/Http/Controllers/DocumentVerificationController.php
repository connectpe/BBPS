<?php

namespace App\Http\Controllers;

use App\Facades\FileUpload;
use App\Models\BusinessInfo;
use App\Models\DocumentVerificationResponse;
use App\Models\UsersBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DocumentVerificationController extends Controller
{
    private $clientID;
    private $clientSecret;
    private $apiVersion;
    private $userId;
    private $user;
    private $completeProfileMessage;

    // Quick Ekyc
    private $docVerifyBaseUrl;
    private $docVerifyTestKey;
    private $docVerifyProductionKey;


    public function __construct()
    {
        $this->clientID = env('CASHFREE_CLIENT_ID');
        $this->clientSecret = env('CASHFREE_CLIENT_SECRET');
        $this->apiVersion = 'V2';
        $this->userId = Auth::id();
        $this->user = Auth::user();
        $this->completeProfileMessage = 'Please complete your profile';

        $this->docVerifyBaseUrl =  config('app.document_verification.DOCUMENT_VERIFY_BASEURL') ?? null;
        $this->docVerifyTestKey =  config('app.document_verification.DOCUMENT_VERIFY_TEST_APIKEY') ?? null;
        $this->docVerifyProductionKey =  config('app.document_verification.DOCUMENT_VERIFY_PRODUTION_APIKEY') ?? null;
    }

    // Function for getting the businessDetails
    protected function getBusinessDetails()
    {
        $userId =   $this->userId;
        return BusinessInfo::where('user_id', $userId)
            ->first();
    }

    // Function for getting the userBank Details
    protected function getBankDetails()
    {
        $userId =   $this->userId;
        return UsersBank::where('user_id', $userId)
            ->first();
    }

    // Function for return response
    protected function returnResponse($status, $message = '')
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function getDocumentData()
    {
        try {

            $user = Auth::user();
            $businessInfo = $user->business;
            $businessInfo = BusinessInfo::select('address', 'pan_number', 'business_pan_number', 'aadhar_number', 'business_pan_name', 'is_pan_verify', 'is_business_pan_verified', 'gst_number', 'is_gstin_verify', 'cin_no', 'is_cin_verify', 'is_bank_details_verify', 'is_aadhaar_verified')->where('user_id', $this->userId)->first();

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

                'pan_number' => $businessInfo->pan_number ?? '-',
                'business_pan_number' => $businessInfo->business_pan_number ?? '-',
                'individual_pan_verified' => (int) $businessInfo->is_pan_verify ?? 0,
                'business_pan_verified' => (int) $businessInfo->is_business_pan_verified ?? 0,

                'gst_number' => $businessInfo->gst_number ?? '-',
                'is_gstin_verify' => (int) $businessInfo->is_gstin_verify ?? 0,

                'cin_no' => $businessInfo->cin_no ?? '-',
                'is_cin_verify' => (int) $businessInfo->is_cin_verify ?? 0,

                'account_number' => $usersBank->account_number ?? '-',
                'ifsc_code' => $usersBank->ifsc_code ?? '-',
                'benificiary_name' => $usersBank->benificiary_name ?? '-',
                'bank_verified' => (int) $businessInfo->is_bank_details_verify ?? 0,

                'aadhar_number' => $businessInfo->aadhar_number ?? '-',
                'is_aadhaar_verified' => (int) $businessInfo->is_aadhaar_verified ?? 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function individualPanVerify(Request $request)
    {
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(
                    false,
                    "Business details are not complete. $this->completeProfileMessage"
                );
            }

            if (!$businessInfo->pan_number) {
                return $this->returnResponse(
                    false,
                    "Individual PAN number doesn't exist."
                );
            }

            if (!$businessInfo->pan_owner_name) {
                return $this->returnResponse(
                    false,
                    "Individual PAN name doesn't exist."
                );
            }

            $endpoint  =  $this->docVerifyBaseUrl . "pan/pan";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $businessInfo->pan_number,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Owner Pan Verification', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'Individual PAN verification failed.'
                );
            }

            if (
                ($data['status_code'] ?? null) === 200 &&
                ($data['status'] ?? null) === 'success'
            ) {
                DB::beginTransaction();

                try {
                    $businessInfo->is_pan_verify = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $businessInfo->user_id,
                        'document_type' => 'owner_pan',
                        'verification_response' => $data,
                    ]);

                    DB::commit();

                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'Individual PAN verification completed.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'Individual PAN verification failed.'
            );
        } catch (\Throwable $e) {
            Log::error('Individual PAN verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function businessPanVerify(Request $request)
    {

        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->business_pan_number) {
                return $this->returnResponse(false, 'Your Business Pan number doesn\'t exist.');
            }

            // if (!$businessInfo->business_pan_name) {
            //     return $this->returnResponse(false, 'Your Business Pan name doesn\'t exist.');
            // }

            $endpoint  =  $this->docVerifyBaseUrl . "pan/pan";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $businessInfo->business_pan_number,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Business Pan Verification', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'Business PAN verification failed.'
                );
            }

            if (
                ($data['status_code'] ?? null) === 200 &&
                ($data['status'] ?? null) === 'success'
            ) {
                DB::beginTransaction();

                try {
                    $businessInfo->is_business_pan_verified = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $businessInfo->user_id,
                        'document_type' => 'business_pan',
                        'verification_response' =>  $data,
                    ]);

                    DB::commit();

                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'Business PAN verification completed.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'Business PAN verification failed.'
            );
        } catch (\Throwable $e) {
            Log::error('Business PAN verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyGstinNumber(Request $request)
    {

        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->gst_number) {
                return $this->returnResponse(false, 'Your GST number doesn\'t exist.');
            }

            $endpoint  =  $this->docVerifyBaseUrl . "corporate/gstin";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $businessInfo->gst_number,
                'filing_status_get' => true
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('GST Verification', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'GST verification failed.'
                );
            }

            if (
                ($data['status_code'] ?? null) === 200 &&
                ($data['status'] ?? null) === 'success'
            ) {
                DB::beginTransaction();

                try {
                    $businessInfo->is_gstin_verify = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $businessInfo->user_id,
                        'document_type' => 'gst',
                        'verification_response' =>  $data,
                    ]);

                    DB::commit();

                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'GST verification completed.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'GST verification failed.'
            );
        } catch (\Throwable $e) {

            Log::error('GST verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyCinNumber(Request $request)
    {

        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->cin_no) {
                return $this->returnResponse(false, 'Your CIN number doesn\'t exist.');
            }

            $endpoint  =  $this->docVerifyBaseUrl . "corporate/company-details";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $businessInfo->cin_no,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('CIN Verification', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'CIN verification failed.'
                );
            }

            if (
                ($data['status_code'] ?? null) === 200 &&
                ($data['status'] ?? null) === 'success'
            ) {
                DB::beginTransaction();

                try {
                    $businessInfo->is_cin_verify = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $businessInfo->user_id,
                        'document_type' => 'cin',
                        'verification_response' =>  $data,
                    ]);

                    DB::commit();

                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'CIN verification completed.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'CIN verification failed.'
            );
        } catch (\Throwable $e) {

            Log::error('CIN verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyAadhaar(Request $request)
    {

        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->aadhar_front_image) {
                return $this->returnResponse(false, 'Aadhaar front image is not uploaded');
            }

            if (!$businessInfo->aadhar_back_image) {
                return $this->returnResponse(false, 'Aadhaar back image is not uploaded');
            }

            if (!$businessInfo->aadhar_name) {
                return $this->returnResponse(false, 'Owner Aadhar name not found');
            }

            if (!$businessInfo->aadhar_number) {
                return $this->returnResponse(false, 'Owner Aadhar number not found');
            }

            $endpoint  =  $this->docVerifyBaseUrl . "aadhaar-v2/generate-otp";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $businessInfo->aadhar_number,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Aadhar Verification OTP Generation', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'Aadhar Verification OTP Generation failed.'
                );
            }

            if (($data['status_code'] ?? null) === 200 && ($data['status'] ?? null) === 'success' && !empty($data['request_id']) && $data['data']['otp_sent']) {
                DB::beginTransaction();

                try {
                    $businessInfo->adhaar_request_id = $data['request_id'] ?? NULL;
                    $businessInfo->save();
                    DB::commit();
                    return response()->json([
                        'status' => true,
                        'otp' => true,
                        'message' => $data['message'] ?? 'Aadhar Verification OTP Send Successfully.'
                    ]);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'Aadhar Verification OTP failed.'
            );
        } catch (\Throwable $e) {

            Log::error('Aadhar Verification OTP error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function submitOTPVerifyAadhaar(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required|numeric|digits:6',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors()->first();
                return $this->returnResponse(false, $message);
            }

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo->adhaar_request_id) {
                return $this->returnResponse(false, 'Initiate aadhar verification from starting');
            }

            $endpoint  =  $this->docVerifyBaseUrl . "aadhaar-v2/submit-otp";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'request_id' => $businessInfo->adhaar_request_id,
                'otp' => $request->otp
            ];


            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Aadhar Verification OTP', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'Aadhar Verification failed.'
                );
            }

            if (($data['status_code'] ?? null) === 200 && ($data['status'] ?? null) === 'success') {
                DB::beginTransaction();

                try {

                    $businessInfo->adhaar_request_id = NULL;
                    $businessInfo->is_aadhaar_verified = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $businessInfo->user_id,
                        'document_type' => 'aadhaar',
                        'verification_response' =>  $data,
                    ]);

                    DB::commit();
                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'Aadhar Verification Completed Successfully.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'Aadhar Verification OTP failed.'
            );
        } catch (\Throwable $e) {

            Log::error('Aadhar Verification OTP error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyBankDetails(Request $request)
    {

        try {

            $businessInfo = $this->getBusinessDetails();
            $bankDetails = $this->getBankDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$bankDetails) {
                return $this->returnResponse(false, "Bank details are not complete. $this->completeProfileMessage");
            }

            if (!$bankDetails->account_number) {
                return $this->returnResponse(false, 'Your bank account number doesn\'t exist.');
            }

            if (!$bankDetails->ifsc_code) {
                return $this->returnResponse(false, 'Your IFSC code doesn\'t exist.');
            }

            if (!$bankDetails->benificiary_name) {
                return $this->returnResponse(false, 'Beneficiary name doesn\'t exist.');
            }

            if (!$bankDetails->account_mobile_number) {
                return $this->returnResponse(false, 'Bank registered mobile number doesn\'t exist.');
            }

            $endpoint  =  $this->docVerifyBaseUrl . "bank-verification";
            // $apiKey  =  $this->docVerifyTestKey;  
            $apiKey  = $this->docVerifyProductionKey;

            $payload = [
                'key' => $apiKey,
                'id_number' => $bankDetails->account_number,
                'ifsc' => $bankDetails->ifsc_code,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            $data = $response->json();

            Log::info('Bank Verification', [
                'response' => $data,
                'endPoint' => $endpoint,
                'http_status' => $response->status(),
            ]);

            if (!$response->successful()) {
                return $this->returnResponse(
                    false,
                    $data['message'] ?? 'Bank verification failed.'
                );
            }

            if (
                ($data['status_code'] ?? null) === 200 &&
                ($data['status'] ?? null) === 'success'
            ) {
                DB::beginTransaction();

                try {
                    $businessInfo->is_bank_details_verify = '1';
                    $businessInfo->save();

                    DocumentVerificationResponse::create([
                        'user_id' => $bankDetails->user_id,
                        'document_type' => 'bank_account',
                        'verification_response' =>  $data,
                    ]);

                    DB::commit();

                    return $this->returnResponse(
                        true,
                        $data['message'] ?? 'Bank verification completed.'
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return $this->returnResponse(
                false,
                $data['message'] ?? 'Bank verification failed.'
            );
        } catch (\Throwable $e) {

            Log::error('Bank verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function initiateVideoKyc(Request $request)
    {
        try {

            $date = '2024-12-01';

            $response = $this->createUserToCashFree($request);

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
                'message' => 'Kyc link generated successfully',
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
