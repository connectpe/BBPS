<?php

namespace App\Http\Controllers;

use App\Facades\FileUpload;
use App\Models\BusinessInfo;
use App\Models\UsersBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentVerificationController extends Controller
{
    private $clientID;
    private $clientSecret;
    private $apiVersion;
    private $userId;
    private $user;
    private $completeProfileMessage;

    public function __construct()
    {
        $this->clientID = env('CASHFREE_CLIENT_ID');
        $this->clientSecret = env('CASHFREE_CLIENT_SECRET');
        $this->apiVersion = 'V2';
        $this->userId = Auth::id();
        $this->user = Auth::user();
        $this->completeProfileMessage = 'Please complete your profile';
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
            $businessInfo = BusinessInfo::select('address', 'pan_number', 'business_pan_number', 'aadhar_number', 'business_pan_name', 'is_pan_verify', 'gst_number', 'is_gstin_verify', 'cin_no', 'is_cin_verify', 'is_bank_details_verify', 'is_aadhaar_verified')->where('user_id', $this->userId)->first();

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
        DB::beginTransaction();
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->cin_no) {
                return $this->returnResponse(false, 'Your CIN number doesn\'t exist.');
            }

            $verificationId = 'CIN' . time();

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret
            ])->post('https://api.cashfree.com/verification/cin', [
                'verification_id' => $verificationId,
                'cin' => $businessInfo->cin_no,

            ]);

            dd($response->json());

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $businessInfo->is_cin_verify = '1';
                $businessInfo->save();
            }

            DB::commit();
            return $this->returnResponse(true, 'CIN verification completed');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyGstinNumber(Request $request)
    {
        DB::beginTransaction();
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->gst_number) {
                return $this->returnResponse(false, 'Your GST number doesn\'t exist.');
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post('https://api.cashfree.com/verification/gstin', [
                "GSTIN" => $businessInfo->gst_number,
            ]);

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $businessInfo->is_gstin_verify = '1';
                $businessInfo->save();
            }
            DB::commit();
            return $this->returnResponse(true, $response['data']['message'] ?? 'GSTIN verification completed');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function individualPanVerify(Request $request)
    {

        DB::beginTransaction();
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->pan_number) {
                return $this->returnResponse(false, 'Individual Pan number doesn\'t exist.');
            }

            if (!$businessInfo->pan_owner_name) {
                return $this->returnResponse(false, 'Individual Pan name doesn\'t exist.');
            }

            $payload = [
                'pan'  => $businessInfo->pan_number,
                'name' => $businessInfo->pan_owner_name,
            ];

            $endpoint = "https://api.cashfree.com/verification/pan";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post($endpoint, $payload);

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $businessInfo->is_pan_verify = '1';
                $businessInfo->save();
            }

            DB::commit();
            return $this->returnResponse(true, $response['data']['message'] ?? 'Individual PAN verification completed');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function businessPanVerify(Request $request)
    {

        DB::beginTransaction();
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->business_pan_number) {
                return $this->returnResponse(false, 'Your Business Pan number doesn\'t exist.');
            }

            if (!$businessInfo->business_pan_name) {
                return $this->returnResponse(false, 'Your Business Pan name doesn\'t exist.');
            }

            $payload = [
                'pan'  => $businessInfo->business_pan_number,
                'name' => $businessInfo->business_pan_name,
            ];

            $endpoint = "https://api.cashfree.com/verification/pan";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post($endpoint, $payload);

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $businessInfo->is_pan_verify = '1';
                $businessInfo->save();
            }

            DB::commit();
            return $this->returnResponse(true, $response['data']['message'] ?? 'Business PAN verification completed');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function verifyIfsc(Request $request)
    {
        DB::beginTransaction();
        try {

            $bankDetails = $this->getBankDetails();

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

            if (!$bankDetails->phone) {
                return $this->returnResponse(false, 'Bank registered mobile number doesn\'t exist.');
            }

            $payload = [
                "bank_account" => $bankDetails->account_number ?? null,
                'ifsc'  => $bankDetails->ifsc_code ?? null,
                "name" => $bankDetails->benificiary_name ?? null,
                "phone" => $bankDetails->account_mobile_number ?? null,
            ];

            // $endpoint = "https://api.cashfree.com/verification/ifsc";
            $endpoint = "https://api.cashfree.com/verification/bank-account/sync";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->post($endpoint, $payload);
            dd($response->json());

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $bankDetails->is_pan_verify = '1';
                $bankDetails->save();
            }

            DB::commit();
            return $this->returnResponse(true, $response['data']['message'] ?? 'IFSC verification completed');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyAadhaar(Request $request)
    {
        DB::beginTransaction();
        try {

            $businessInfo = $this->getBusinessDetails();

            if (!$businessInfo) {
                return $this->returnResponse(false, "Business details are not complete. $this->completeProfileMessage");
            }

            if (!$businessInfo->aadhar_front_image) {
                return $this->returnResponse(false, 'Aadhaar image is not uploaded');
            }

            $relativePath = $businessInfo->aadhar_front_image;
            $path = storage_path('app/public/' . $relativePath);

            $imageContent = file_get_contents($path);

            if ($imageContent === false) {
                return $this->returnResponse(false, 'Unable to read Aadhaar image');
            }

            $verificationId = 'AADHAAR_' . time();
            $endpoint = "https://api.cashfree.com/verification/aadhaar-masking";


            $response = Http::withHeaders([
                'x-client-id' => $this->clientID,
                'x-client-secret' => $this->clientSecret,
            ])->attach(
                'image',
                $imageContent,
                'aadhaar.jpg'
            )->post($endpoint, [
                'verification_id' => $verificationId,
            ]);

            if (isset($response['status'], $response['data']['valid']) && $response['status'] === true && $response['data']['valid'] === true) {
                $businessInfo->is_aadhaar_verified = '1';
                $businessInfo->save();
            }

            DB::commit();
            return $this->returnResponse(true, $response['data']['message'] ?? 'Aadhaar verification completed');
        } catch (\Exception $e) {
            DB::commit();
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

    public function createUserToCashFree(Request $request)
    {
        try {

            $user_id = 'USER' . $this->userId;
            $date = '2024-12-01';
            $payload = [
                'name' => $this->user->name ?? '-',
                'email' => $this->user->email ?? '-',
                'phone' => $this->user->mobile ?? '-',
                'address' => $businessInfo->address ?? '-',
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
}
