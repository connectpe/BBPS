<?php

namespace App\Http\Controllers\users;

use App\Facades\FileUpload;
use App\Helpers\CommonHelper;
use App\Models\Agreement;
use App\Helpers\NSDLHelper;
use App\Helpers\SendingMail;
use App\Http\Controllers\Controller;
use App\Models\BusinessInfo;
use App\Models\GlobalService;
use App\Models\IpWhitelist;
use App\Models\LoadMoneyRequest;
use App\Models\NsdlPayment;
use App\Models\OauthUser;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserRooting;
use App\Models\UsersBank;
use App\Models\UserService;
use App\Models\WebHookUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function bbpsUsers()
    {
        $users = User::select('id', 'name', 'email')->where('role_id', '!=', '1')->where('role_id', '!=', '4')->where('status', '!=', '0')->orderBy('id', 'desc')->get();

        return view('Users.users', compact('users'));
    }

    public function dashboard()
    {
        return view('Dashboard.user-dashboard');
    }


    public function redirectToKycPage()
    {
        return view('Users.kyc-page');
    }


    public function redirectTounauthrized()
    {
        return view('errors.unauthrized');
    }

    public function ajaxBbpsUsers(Request $request)
    {
        $users = [];
        $gendersArray = ['Male', 'Female', 'Other'];

        for ($i = 1; $i <= 100; $i++) {

            $randomGenderKey = array_rand($gendersArray);
            $userGender = $gendersArray[$randomGenderKey];

            $users[] = [
                'id' => $i,
                'contact_name' => "User $i",
                'email' => "user$i@test.com",
                'mobile' => rand(9999999999, 1111111111),
                'gender' => "$userGender",
                'aadhaar' => rand(999999999999, 111111111111),
                'pan' => strtoupper(Str::random(10)),
                'status' => $i % 2 == 0 ? 'Active' : 'Inactive',
            ];
        }

        if (! empty($request->name)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['name']), strtolower($request->name)));
        }
        if (! empty($request->email)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['email']), strtolower($request->email)));
        }
        if (! empty($request->status)) {
            $users = array_filter($users, fn($u) => $u['status'] == $request->status);
        }

        $filteredCount = count($users);

        //  Pagination (AJAX)
        $users = array_slice($users, $request->start, $request->length);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => 100,
            'recordsFiltered' => $filteredCount,
            'data' => array_values($users),
        ]);
    }

    public function completeProfile(Request $request, $userId)
    {

        DB::beginTransaction();
        try {


            $businessData = BusinessInfo::where('user_id', $userId)->first();
            $bankDetail = UsersBank::where('user_id', $userId)->first();
            $user = User::find($userId);

            // short function for the image validation check 
            $requiredBasedOnKyc = fn($existing) => $existing === '1' ? 'nullable|' : 'required|';   // check if kyc verified then nullable othewise required,
            $requiredIfMissing = fn($existing) => empty($existing) ? 'required|' : 'nullable|';    // check if value exists then nullable othewise required,
            $requiredIfMissingOrCondition = fn($existing, $condition) => (empty($existing) && $condition) ? 'required|' : 'nullable|';   // this is for the condtional based

            $validator = Validator::make(
                $request->all(),
                [
                    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'business_name' => 'required|string|max:255|regex:/^(?!.*([.,@-])\1{2,}).*$/|regex:/^[a-zA-Z0-9\s&.,-]+$/',
                    'business_category' => 'required|exists:business_categories,id',
                    'business_type' => 'required|string|max:255|regex:/^(?!.*([.,@-])\1{2,}).*$/|regex:/^[a-zA-Z0-9\s&.,-]+$/',

                    'cin_number' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:50|unique:business_infos,cin_no,' . ($businessData->id ?? 'NULL') . ',id|regex:/^[A-Z]{1}[0-9]{5}[A-Z]{2}[0-9]{4}[A-Z]{3}[0-9]{6}$/i',
                    'gst_number' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:50|unique:business_infos,gst_number,' . ($businessData->id ?? 'NULL') . ',id|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                    'business_pan' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:50|unique:business_infos,business_pan_number,' . ($businessData->id ?? 'NULL') . ',id|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i',
                    'business_email' => 'required|email|max:255|unique:business_infos,business_email,' . ($businessData->id ?? 'NULL') . ',id',
                    'business_phone' => 'required|string|max:20|unique:business_infos,business_phone,' . ($businessData->id ?? 'NULL') . ',id|regex:/^[6-9]\d{9}$/',

                    'state' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'pincode' => 'required|string|max:10',
                    'business_address' => 'required|string|max:500|regex:/^(?!.*([.,@-])\1{2,}).*$/|regex:/^[a-zA-Z0-9\s&.,-]+$/',

                    'adhar_number' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:20|regex:/^\d{12}$/|unique:business_infos,aadhar_number,' . ($businessData->id ?? 'NULL') . ',id',
                    'pan_number' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:20|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i|unique:business_infos,pan_number,' . ($businessData->id ?? 'NULL') . ',id',
                    'adhar_front_image' => $requiredIfMissing($businessData->aadhar_front_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'adhar_back_image' => $requiredIfMissing($businessData->aadhar_back_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'pan_card_image' => $requiredIfMissing($businessData->pancard_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',

                    'account_holder_name' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:255|regex:/^(?!.*([.,@-])\1{2,}).*$/|regex:/^[a-zA-Z0-9\s&.,-]+$/',
                    'account_number' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:30|unique:users_banks,account_number,' . ($bankDetail->id ?? 'NULL') . ',id',
                    'ifsc_code' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:20',
                    'branch_name' => $requiredBasedOnKyc($businessData->is_kyc ?? '0') . 'string|max:255',
                    'bank_docs' => $requiredIfMissing($bankDetail->bank_docs ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',

                    // Added in Later
                    'itr_filled' => 'required|in:1,0',
                    'itr_not_reason' => 'required_if:itr_filled,0|max:300',
                    'itr_filled_image' => $requiredIfMissingOrCondition($businessData->itr_file_image ?? null, $request->itr_filled === '1')
                        . 'file|mimes:jpg,jpeg,png|max:2048',

                    'individual_photo' => $requiredIfMissing($businessData->individual_photo ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'business_pan_image' => $requiredIfMissing($businessData->business_pan_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'registration_certificate_image' => $requiredIfMissing($businessData->registration_certificate_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',

                    'gst_registration_certificate_image' => $requiredIfMissing($businessData->gst_registration_certificate_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'inside_image' => $requiredIfMissing($businessData->inside_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'outside_image' => $requiredIfMissing($businessData->outside_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'business_address_proof_image' => $requiredIfMissing($businessData->outside_image ?? null) . 'file|mimes:jpg,jpeg,png|max:2048',
                    'signed_moa_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                    'signed_aoa_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                    'board_resolution' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                    'nsdl_declaration' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',

                ],
                [
                    'profile_image.image' => 'Profile image must be an image file.',
                    'profile_image.mimes' => 'Profile image must be a file of type: jpeg, png, jpg.',
                    'profile_image.max' => 'Profile image size must not exceed 2MB.',

                    'business_name.required' => 'Business name is required.',
                    'business_name.string' => 'Business name must be a valid string.',
                    'business_name.max' => 'Business name must not exceed 255 characters.',

                    'business_type.required' => 'Business type is required.',
                    'business_type.string' => 'Business type must be a valid string.',
                    'business_type.max' => 'Business type must not exceed 255 characters.',

                    'business_category.required' => 'Business Category is required',
                    'business_category.exists' => 'Invalid Business Category',

                    'industry.string' => 'Industry must be a valid string.',
                    'industry.max' => 'Industry must not exceed 255 characters.',

                    'cin_number.string' => 'CIN number must be a valid string.',
                    'cin_number.max' => 'CIN number must not exceed 50 characters.',
                    'cin_number.unique' => 'This CIN number has already been taken.',
                    'cin_number.regex' => 'The CIN number must be a valid Indian CIN format.',

                    'gst_number.required' => 'GST number is required.',
                    'gst_number.string' => 'GST number must be a valid string.',
                    'gst_number.max' => 'GST number must not exceed 50 characters.',
                    'gst_number.unique' => 'This GST number has already been taken.',
                    'gst_number.regex' => 'The GST number must be a valid Indian GST format (15 characters).',

                    'business_pan.required' => 'Business PAN is required.',
                    'business_pan.string' => 'Business PAN must be a valid string.',
                    'business_pan.max' => 'Business PAN must not exceed 50 characters.',
                    'business_pan.regex' => 'The PAN number must be a valid Indian PAN format (e.g., ABCDE1234F).',
                    'business_pan.unique' => 'This Business PAN number has already been taken.',

                    'business_email.email' => 'Business email must be a valid email address.',
                    'business_email.max' => 'Business email must not exceed 255 characters.',
                    'business_email.unique' => 'This Business Email has already been taken.',

                    'business_phone.string' => 'Business phone must be a valid string.',
                    'business_phone.max' => 'Business phone must not exceed 20 characters.',
                    'business_phone.unique' => 'This Business Phone has already been taken.',
                    'business_phone.regex' => 'The phone number must be a valid 10-digit Indian mobile number starting with 6-9.',

                    'state.required' => 'State is required.',
                    'state.string' => 'State must be a valid string.',
                    'state.max' => 'State must not exceed 255 characters.',

                    'city.required' => 'City is required.',
                    'city.string' => 'City must be a valid string.',
                    'city.max' => 'City must not exceed 255 characters.',

                    'pincode.required' => 'Pincode is required.',
                    'pincode.string' => 'Pincode must be a valid string.',
                    'pincode.max' => 'Pincode must not exceed 10 characters.',

                    'business_address.required' => 'Business address is required.',
                    'business_address.string' => 'Business address must be a valid string.',
                    'business_address.max' => 'Business address must not exceed 500 characters.',

                    'adhar_number.required' => 'Aadhar number is required.',
                    'adhar_number.string' => 'Aadhar number must be a valid string.',
                    'adhar_number.max' => 'Aadhar number must not exceed 20 characters.',
                    'adhar_number.unique' => 'This Aadhaar has already been taken.',
                    'adhar_number.regex' => 'The Aadhaar number must be exactly 12 digits.',

                    'pan_number.regex' => 'The PAN number must be in a valid format (e.g., ABCDE1234F).',
                    'pan_number.required' => 'PAN number is required.',
                    'pan_number.string' => 'PAN number must be a valid string.',
                    'pan_number.max' => 'PAN number must not exceed 20 characters.',
                    'pan_number.unique' => 'This Pan has already been taken.',

                    'adhar_front_image.image' => 'Aadhar front image must be an image file.',
                    'adhar_front_image.mimes' => 'Aadhar front image must be a file of type: jpeg, png, jpg.',
                    'adhar_front_image.max' => 'Aadhar front image size must not exceed 2MB.',

                    'adhar_back_image.image' => 'Aadhar back image must be an image file.',
                    'adhar_back_image.mimes' => 'Aadhar back image must be a file of type: jpeg, png, jpg.',
                    'adhar_back_image.max' => 'Aadhar back image size must not exceed 2MB.',

                    'pan_card_image.image' => 'PAN card image must be an image file.',
                    'pan_card_image.mimes' => 'PAN card image must be a file of type: jpeg, png, jpg.',
                    'pan_card_image.max' => 'PAN card image size must not exceed 2MB.',

                    'account_holder_name.required' => 'Account holder name is required.',
                    'account_holder_name.string' => 'Account holder name must be a valid string.',
                    'account_holder_name.max' => 'Account holder name must not exceed 255 characters.',

                    'account_number.required' => 'Account number is required.',
                    'account_number.string' => 'Account number must be a valid string.',
                    'account_number.max' => 'Account number must not exceed 30 characters.',
                    'account_number.unique' => 'This Account number has already been taken.',

                    'ifsc_code.required' => 'IFSC code is required.',
                    'ifsc_code.string' => 'IFSC code must be a valid string.',
                    'ifsc_code.max' => 'IFSC code must not exceed 20 characters.',

                    'branch_name.required' => 'Branch name is required.',
                    'branch_name.string' => 'Branch name must be a valid string.',
                    'branch_name.max' => 'Branch name must not exceed 255 characters.',

                    'bank_docs.file' => 'Each bank document must be a valid file.',
                    'bank_docs.mimes' => 'Bank documents must be a file of type: pdf, jpg, png.',
                    'bank_docs.max' => 'Bank documents must not exceed 5MB each.',

                    // Added in Later

                    // ITR
                    'itr_filled.required' => 'Please select whether ITR is filed or not.',
                    'itr_filled.in' => 'Invalid selection for ITR status.',

                    'itr_not_reason.required_if' => 'Please provide a reason if ITR is not filed.',
                    'itr_not_reason.max' => 'ITR not filed reason must not exceed 300 characters.',

                    'itr_filled_image.required_if' => 'Please upload ITR document when ITR is filed.',
                    'itr_filled_image.file' => 'ITR document must be a valid file.',
                    'itr_filled_image.mimes' => 'ITR document must be a file of type: jpg, jpeg, png.',
                    'itr_filled_image.max' => 'ITR document must not exceed 2MB.',

                    // Individual Photo
                    'individual_photo.required' => 'Individual photo is required.',
                    'individual_photo.file' => 'Individual photo must be a valid file.',
                    'individual_photo.mimes' => 'Individual photo must be a file of type: jpg, jpeg, png.',
                    'individual_photo.max' => 'Individual photo must not exceed 2MB.',

                    // Business PAN
                    'business_pan_image.required' => 'Business PAN image is required.',
                    'business_pan_image.file' => 'Business PAN image must be a valid file.',
                    'business_pan_image.mimes' => 'Business PAN image must be a file of type: jpg, jpeg, png.',
                    'business_pan_image.max' => 'Business PAN image must not exceed 2MB.',

                    // Registration Certificate
                    'registration_certificate_image.required' => 'Registration certificate is required.',
                    'registration_certificate_image.file' => 'Registration certificate must be a valid file.',
                    'registration_certificate_image.mimes' => 'Registration certificate must be a file of type: jpg, jpeg, png.',
                    'registration_certificate_image.max' => 'Registration certificate must not exceed 2MB.',

                    // GST
                    'gst_registration_certificate_image.file' => 'GST certificate must be a valid file.',
                    'gst_registration_certificate_image.mimes' => 'GST certificate must be a file of type: jpg, jpeg, png.',
                    'gst_registration_certificate_image.max' => 'GST certificate must not exceed 2MB.',

                    // Business Address Proof
                    'business_address_proof_image.file' => 'Business Address Proof must be a valid file.',
                    'business_address_proof_image.mimes' => 'Business Address Proof must be a file of type: jpg, jpeg, png.',
                    'business_address_proof_image.max' => 'Business Address Proof must not exceed 2MB.',

                    // Inside Image
                    'inside_image.required' => 'Inside image of business premises is required.',
                    'inside_image.file' => 'Inside image must be a valid file.',
                    'inside_image.mimes' => 'Inside image must be a file of type: jpg, jpeg, png.',
                    'inside_image.max' => 'Inside image must not exceed 2MB.',

                    // Outside Image
                    'outside_image.required' => 'Outside image of business premises is required.',
                    'outside_image.file' => 'Outside image must be a valid file.',
                    'outside_image.mimes' => 'Outside image must be a file of type: jpg, jpeg, png.',
                    'outside_image.max' => 'Outside image must not exceed 2MB.',

                    // MOA
                    'signed_moa_image.file' => 'Signed MOA must be a valid file.',
                    'signed_moa_image.mimes' => 'Signed MOA must be a file of type: jpg, jpeg, png.',
                    'signed_moa_image.max' => 'Signed MOA must not exceed 2MB.',

                    // AOA
                    'signed_aoa_image.file' => 'Signed AOA must be a valid file.',
                    'signed_aoa_image.mimes' => 'Signed AOA must be a file of type: jpg, jpeg, png.',
                    'signed_aoa_image.max' => 'Signed AOA must not exceed 2MB.',

                    // Board Resolution
                    'board_resolution.file' => 'Board resolution must be a valid file.',
                    'board_resolution.mimes' => 'Board resolution must be a file of type: jpg, jpeg, png.',
                    'board_resolution.max' => 'Board resolution must not exceed 2MB.',

                    // NSDL
                    'nsdl_declaration.file' => 'NSDL declaration must be a valid file.',
                    'nsdl_declaration.mimes' => 'NSDL declaration must be a file of type: jpg, jpeg, png.',
                    'nsdl_declaration.max' => 'NSDL declaration must not exceed 2MB.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $profilePicPath = $user->profile_image ?? null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = FileUpload::uploadFile($request->profile_image, "profile_pictures/$userId", $user->profile_image ?? null);
                User::where('id', $userId)->update(['profile_image' => $profilePicPath]);
            }


            $adharFrontPath = $businessData->aadhar_front_image ?? null;
            if ($request->hasFile('adhar_front_image')) {
                $adharFrontPath = FileUpload::uploadFile($request->adhar_front_image, "kyc_documents/$userId", $businessData->aadhar_front_image ?? null);
            }

            $adharBackPath = $businessData->aadhar_back_image ?? null;
            if ($request->hasFile('adhar_back_image')) {
                $adharBackPath = FileUpload::uploadFile($request->adhar_back_image, "kyc_documents/$userId", $businessData->aadhar_back_image ?? null);
            }

            $panCardPath = $businessData->pancard_image ?? null;
            if ($request->hasFile('pan_card_image')) {
                $panCardPath = FileUpload::uploadFile($request->pan_card_image, "kyc_documents/$userId", $businessData->pancard_image ?? null);
            }

            $bankDocsPath = $bankDetail->bank_docs ?? null;
            if ($request->hasFile('bank_docs')) {
                $bankDocsPath = FileUpload::uploadFile($request->bank_docs, "bank_documents/$userId", $bankDetail->bank_docs ?? null);
            }

            // Added in Lated

            $individualPhoto = $businessData->individual_photo ?? null;
            if ($request->hasFile('individual_photo')) {
                $individualPhoto = FileUpload::uploadFile($request->individual_photo, "kyc_documents/$userId", $businessData->individual_photo ?? null);
            }

            $businessPanImage = $businessData->business_pan_image ?? null;
            if ($request->hasFile('business_pan_image')) {
                $businessPanImage = FileUpload::uploadFile($request->business_pan_image, "kyc_documents/$userId", $businessData->business_pan_image ?? null);
            }

            $registrationCertificate = $businessData->registration_certificate_image ?? null;
            if ($request->hasFile('registration_certificate_image')) {
                $registrationCertificate = FileUpload::uploadFile($request->registration_certificate_image, "kyc_documents/$userId", $businessData->registration_certificate_image ?? null);
            }

            $gstRegistrationCertificate = $businessData->gst_registration_certificate_image ?? null;
            if ($request->hasFile('gst_registration_certificate_image')) {
                $gstRegistrationCertificate = FileUpload::uploadFile($request->gst_registration_certificate_image, "kyc_documents/$userId", $businessData->gst_registration_certificate_image ?? null);
            }

            $businessAddressProof = $businessData->business_address_proof_image ?? null;
            if ($request->hasFile('business_address_proof_image')) {
                $businessAddressProof = FileUpload::uploadFile($request->business_address_proof_image, "kyc_documents/$userId", $businessData->business_address_proof_image ?? null);
            }

            $insideImage = $businessData->inside_image ?? null;
            if ($request->hasFile('inside_image')) {
                $insideImage = FileUpload::uploadFile($request->inside_image, "kyc_documents/$userId", $businessData->inside_image ?? null);
            }

            $outsideImage = $businessData->outside_image ?? null;
            if ($request->hasFile('outside_image')) {
                $outsideImage = FileUpload::uploadFile($request->outside_image, "kyc_documents/$userId", $businessData->outside_image ?? null);
            }

            $signedMOAImage = $businessData->signed_moa_image ?? null;
            if ($request->hasFile('signed_moa_image')) {
                $signedMOAImage = FileUpload::uploadFile($request->signed_moa_image, "kyc_documents/$userId", $businessData->signed_moa_image ?? null);
            }

            $signedAOAImage = $businessData->signed_aoa_image ?? null;
            if ($request->hasFile('signed_aoa_image')) {
                $signedAOAImage = FileUpload::uploadFile($request->signed_aoa_image, "kyc_documents/$userId", $businessData->signed_aoa_image ?? null);
            }

            $boardResolutionImage = $businessData->board_resoultion_image ?? null;
            if ($request->hasFile('board_resolution')) {
                $boardResolutionImage = FileUpload::uploadFile($request->board_resolution, "kyc_documents/$userId", $businessData->board_resoultion_image ?? null);
            }

            $nsdlDocument = $businessData->nsdl_declaration_image ?? null;
            if ($request->hasFile('nsdl_declaration')) {
                $nsdlDocument = FileUpload::uploadFile($request->nsdl_declaration, "kyc_documents/$userId", $businessData->nsdl_declaration_image ?? null);
            }

            $itrFilledImage = $businessData->itr_file_image ?? null;
            if ($request->hasFile('itr_filled_image')) {
                $itrFilledImage = FileUpload::uploadFile($request->itr_filled_image, "kyc_documents/$userId", $businessData->itr_file_image ?? null);
            }


            // Added in Lated

            $data = [
                'business_name' => $request->business_name,
                'cin_no' => $request->cin_number,
                'gst_number' => $request->gst_number,
                'business_pan_number' => $request->business_pan,
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_type' => $request->business_type,
                'business_category_id' => $request->business_category,
                'aadhar_number' => $request->adhar_number,

                'pan_number' => $request->pan_number,
                'address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,

                'aadhar_front_image' => $adharFrontPath,
                'aadhar_back_image' => $adharBackPath,
                'pancard_image' => $panCardPath,

                // Added in Lated 
                'itr_filled' => $request->itr_filled,
                'itr_not_reason' => $request->itr_filled == 0 ? $request->itr_not_reason : NULL,

                'individual_photo' => $individualPhoto,
                'business_pan_image' => $businessPanImage,
                'registration_certificate_image' => $registrationCertificate,
                'gst_registration_certificate_image' => $gstRegistrationCertificate,
                'business_address_proof_image' => $businessAddressProof,
                'inside_image' => $insideImage,
                'outside_image' => $outsideImage,
                'signed_moa_image' => $signedMOAImage,
                'signed_aoa_image' => $signedAOAImage,
                'board_resoultion_image' => $boardResolutionImage,
                'nsdl_declaration_image' => $nsdlDocument,
                'itr_file_image' => $itrFilledImage,

            ];

            if ($businessData?->is_kyc === '1') {
                $removedToElements = [
                    'cin_no',
                    'gst_number',
                    'business_pan_number',
                    'aadhar_number',
                    'pan_number',
                    'aadhar_front_image',
                    'aadhar_back_image',
                    'pancard_image',
                    'individual_photo',
                    'business_pan_image',
                    'registration_certificate_image',
                    'gst_registration_certificate_image',
                    'business_address_proof_image',
                    'signed_moa_image',
                    'signed_aoa_image',
                    'board_resoultion_image',
                    'nsdl_declaration_image',
                ];
                $data = array_diff_key($data, array_flip($removedToElements));
            }


            $businessInfo = BusinessInfo::updateOrCreate([
                'user_id' => $userId,
            ], $data);

            if ($businessData?->is_kyc === '0' || empty($businessData)) {
                UsersBank::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'business_info_id' => $businessInfo->id,
                    ],
                    [
                        'benificiary_name' => $request->account_holder_name,
                        'branch_name' => $request->branch_name,
                        'account_number' => $request->account_number,
                        'ifsc_code' => $request->ifsc_code,
                        'bank_docs' => $bankDocsPath,
                    ]
                );
            }

            Cache::forget("profile:{$userId}:userdata");
            Cache::forget("profile:{$userId}:businessInfo");
            Cache::forget("profile:{$userId}:usersBank");

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Profile completed successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateClientCredentials(Request $request)
    {

        if (! auth()->check() && auth::user()->role_id != '2') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'service' => 'required|string|max:50',
        ]);

        // dd(auth()->id());

        DB::beginTransaction();

        $service = GlobalService::where('slug', $request->service)->select('id')->first();
        if (! $service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
            ], 404);
        }
        $userId = auth()->id();
        $isEnableService = UserService::where('user_id', $userId)->where('service_id', $service->id)->where('status', 'approved')->where('is_active', '1')->first();

        if (! $isEnableService) {
            return response()->json([
                'status' => false,
                'message' => $request->service . 'is not enable or approved by the admin',
            ], 401);
        }
        // dd($service);

        try {

            $userId = auth()->id();
            $clientId = 'RAFI' . strtoupper($request->service) . '_' . Str::random(16);
            $plainSecret = Str::random(32);
            $hashedSecret = hash('sha512', $plainSecret);

            $secretCount = OauthUser::where('user_id', $userId)
                ->where('service_id', $service->id)
                ->update(['is_active' => '0']);

            if ($secretCount > 1) {
                OauthUser::where('user_id', $userId)
                    ->where('service_id', $service->id)
                    ->update(['is_active' => '0']);
            }

            $credential = OauthUser::create([
                'user_id' => $userId,
                'service_id' => $service->id,
                'client_id' => $clientId,
                'client_secret' =>$hashedSecret,
                'is_active' => '1',
            ]);
            Cache::forget("profile:{$userId}:saltKeys");
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Client credentials generated successfully',
                'data' => [
                    'client_id' => $credential->client_id,
                    'client_secret' => $plainSecret,
                ],
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Client credential generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while generating credentials',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewSingleUsers($Id)
    {
        try {

            if (!auth()->check() || (!in_array(auth()->user()->role_id, [1, 4]))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            CommonHelper::checkAuthUser();
            $userId = $Id;



            $data['userData'] = User::where('id', $userId)
                ->select('id', 'name', 'email', 'mobile', 'status', 'role_id')
                ->firstOrFail();

            $data['businessInfo'] = BusinessInfo::where('user_id', $userId)->first();
            $data['usersBank'] = UsersBank::where('user_id', $userId)->first();

            $data['serviceEnabled'] = UserService::where('user_id', $userId)->where('status', 'approved')->where('is_active', '1')->get();
            $data['serviceRequest'] = UserService::where('user_id', $userId)->where('status', 'pending')->where('is_active', '1')->get();

            $enabledServiceIds = UserService::where('user_id', $userId)
                ->where('status', 'approved')
                ->where('is_active', '1')
                ->pluck('service_id');

            $data['globalServices'] = GlobalService::where('is_active', '1')
                ->whereIn('id', $enabledServiceIds)
                ->select('id', 'service_name', 'slug')
                ->orderBy('service_name')
                ->get();

            $data['userRootings'] = UserRooting::where('user_id', $userId)->get()->keyBy('service_id');

            return view('Users.view-user')->with($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getProvidersByService(Request $request, $service_id)
    {
        try {
            $providers = Provider::where('service_id', $service_id)->get();

            return response()->json([
                'status' => true,
                'providers' => $providers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveUserRouting(Request $request, $id)
    {
        $request->validate([
            'service_id' => 'required|exists:global_services,id',
            'provider_slug' => 'required|string|max:255',
        ]);

        try {
            $userId = decrypt($id);
            $userRouting = UserRooting::updateOrCreate(
                [
                    'user_id' => $userId,
                    'service_id' => $request->service_id,
                ],
                [
                    'provider_slug' => $request->provider_slug,
                    'service_unique_id' => null,
                    'updated_at' => now(),
                ]
            );

            \Log::info('UserRouting saved', [
                'user_id' => $userId,
                'service_id' => $request->service_id,
                'provider_slug' => $request->provider_slug,
                'created' => $userRouting->wasRecentlyCreated,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Routing configuration saved successfully!',
                'data' => $userRouting,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error saving user routing', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'service_id' => $request->service_id ?? null,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getServiceProviders(Request $request, $serviceId)
    {
        // dd($request->all(),$serviceId);
        try {
            $providers = Provider::where('service_id', $serviceId)
                ->where('is_active', '1')
                ->select('id', 'provider_name as name', 'provider_slug as slug')
                ->orderBy('provider_name')
                ->get();
            // dd($providers);
            return response()->json([
                'status' => true,
                'data' => $providers,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching providers', [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error fetching providers: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function ApiLog()
    {

        $users = User::select('id', 'name', 'email')->where('role_id', '!=', '1')->where('status', '!=', '0')->orderBy('id', 'desc')->get();

        return view('Users.api-log', compact('users'));
    }

    // Ip Whitelist

    public function addIpWhiteList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'service_id' => 'required|exists:global_services,id',
        ], [
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'service_id.required' => 'Please select a service.',
            'service_id.exists' => 'Selected service is invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();

            $ipCount = IpWhitelist::where('user_id', $userId)
                ->where('service_id', $request->service_id)
                ->where('is_deleted', '0')
                ->count();

            if ($ipCount >= 5) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot add more than 5 IP addresses for this service.',
                ]);
            }

            $duplicateIp = IpWhitelist::where('user_id', $userId)->where('service_id', $request->service_id)->where('ip_address', $request->ip_address)->where('is_deleted', '0')->count();

            if ($duplicateIp) {
                return response()->json([
                    'status' => false,
                    'message' => 'This IP is already whitelisted for the selected service.',
                ]);
            }

            $data = [
                'user_id' => $userId,
                'ip_address' => $request->ip_address,
                'service_id' => $request->service_id,
                'updated_by' => $userId,
                'is_active' => '1',
            ];
            IpWhitelist::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'IP address whitelisted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'System Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editIpWhiteList(Request $request, $Id)
    {
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'service_id' => 'required|exists:global_services,id',
        ], [
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $userId = Auth::user()->id;
            $ip = IpWhitelist::find($Id);

            if (! $ip) {
                return response()->json(['status' => false, 'message' => 'Record not found or access denied']);
            }

            // Duplicate Check (Ignore current record ID)
            $duplicateIp = IpWhitelist::where('user_id', $userId)
                ->where('service_id', $request->service_id)
                ->where('ip_address', $request->ip_address)
                ->where('id', '!=', $Id)
                ->where('is_deleted', '0')
                ->exists();
            if ($duplicateIp > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate Ip for selected Service'
                ]);
            }

            $data = [
                'ip_address' => $request->ip_address,
                'service_id' => $request->service_id,
                'updated_by' => $userId,
            ];

            $ip->update($data);

            DB::commit();

            return response()->json(['status' => true, 'message' => 'IP address Updated Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function statusIpWhiteList($Id)
    {

        DB::beginTransaction();

        try {

            $userId = Auth::user()->id;
            $ip = IpWhitelist::find($Id);

            if (! $ip || $ip->user_id != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access or IP not found',
                ]);
            }

            if (! $ip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ip address not Found',
                ]);
            }

            $data = [
                'is_active' => $ip->is_active == '1' ? '0' : '1',
                'updated_by' => $userId,
            ];

            $update = $ip->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status Changed Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteIpWhiteList($Id)
    {

        DB::beginTransaction();

        try {

            $userId = Auth::user()->id;
            $ip = IpWhitelist::find($Id);

            if (! $ip || $ip->user_id != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access or IP not found',
                ]);
            }

            if (! $ip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ip address not Found',
                ]);
            }

            $data = [
                'is_deleted' => '1',
                'updated_by' => $userId,
            ];

            $update = $ip->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Ip Deleted Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function generateMpin(Request $request)
    {
        DB::beginTransaction();

        try {
            if (Auth::user()->role_id != '2') {
                return response()->json([
                    'status' => false,
                    'message' => 'You are unauthorized',
                ], 403);
            }

            $request->validate([
                'current_mpin' => ['required', 'digits:4', 'numeric'],
                'new_mpin' => ['required', 'digits:4', 'numeric', 'different:current_mpin'],
                'new_mpin_confirmation' => ['required', 'same:new_mpin'],
            ]);



            $user = Auth::user();

            if (! Hash::check($request->current_mpin, $user->mpin)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current MPIN is incorrect',
                ]);
            }

            User::where('id', $user->id)->update([
                'mpin' => Hash::make($request->new_mpin),
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'MPIN updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function addWebHookUrl(Request $request)
    {

        $userId = Auth::id();
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'service_id' => [
                'required',
                'exists:global_services,id',
                Rule::unique(WebHookUrl::class)->where(function ($query) use ($request, $userId) {
                    return $query->where('user_id', $userId)
                        ->where('service_id', $request->service_id);
                }),
            ],
        ], [
            'url.required' => 'The URL field is required.',
            'url.url' => 'Please enter a valid URL.',
            'service_id.unique' => 'You have already assigned a URL to this service.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $service = GlobalService::find($request->service_id);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }

            $data = [
                'user_id' => $userId,
                'url' => $request->url,
                'service_id' => $request->service_id,
                'service_slug' =>  $service->slug,
                'updated_by' => $userId,
            ];


            $webhook = WebHookUrl::create($data);

            Cache::forget("profile:{$userId}:webhookUrl");
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Url Added Successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateWebHookUrl(Request $request)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'edit_url' => 'required|url',
            'url_id' => 'required',
            'edit_service_id' => [
                'required',
                'exists:global_services,id',

                Rule::unique(WebHookUrl::class, 'service_id')->where(function ($query) use ($request, $userId) {
                    return $query->where('user_id', $userId)
                        ->where('service_id', $request->edit_service_id);
                })->ignore($request->url_id),
            ],
        ], [
            'edit_url.required' => 'The URL field is required.',
            'edit_url.url' => 'Please enter a valid URL.',
            'edit_service_id.unique' => 'You have already assigned a URL to this service.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $webhook = WebHookUrl::where('user_id', $userId)->find($request->url_id);

            if (!$webhook) {
                return response()->json([
                    'status' => false,
                    'message' => 'Webhook URL not found or unauthorized.',
                ], 404);
            }

            $service = GlobalService::find($request->edit_service_id);

            // 3. Update the data
            $webhook->update([
                'url'          => $request->edit_url,
                'service_id'   => $request->edit_service_id,
                'service_slug' => $service->slug,
                'updated_by'   => $userId,
            ]);

            Cache::forget("profile:{$userId}:webhookUrl");
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Url Updated Successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function initiateNsdlPayment(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
            $user = auth()->user();
            $txnId = 'PAY' . time() . rand(1000, 9999);
            $payload = [
                'name' => $user->name . ' Chauhan',
                'amount' => $request->amount,
                'mobile' => $user->mobile,
                'transaction_id' => $txnId,
            ];
            dd($payload);
            $api = NSDLHelper::processOrderCreation($payload);
            dd($api);
            $orderId = $api['data']['order_id'] ?? $api['order_id'] ?? null;
            $qrString = $api['data']['qr_string'] ?? $api['qr_string'] ?? null;
            $qrUrl = $api['data']['qr_url'] ?? $api['qr_url'] ?? null;
            NsdlPayment::create([
                'user_id' => $user->id,
                'service_id' => null,
                'mobile_no' => $user->mobile,
                'amount' => $request->amount,
                'transaction_id' => $txnId,
                'order_id' => $orderId,
                'status' => 'initiated',
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment Initiated Successfully',
                'data' => [
                    'transaction_id' => $txnId,
                    'order_id' => $orderId,
                    'qr_string' => $qrString,
                    'qr_url' => $qrUrl,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function addMoneyRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'utr_no' => 'required|alpha_num|min:10|max:20',
            'request_image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'remark' => 'nullable|regex:/^[\w\s\p{P}\-]+$/u|max:300'
        ], [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',

            'utr_no.required' => 'UTR number is required.',
            'utr_no.alpha_num' => 'UTR number must be alphanumeric.',
            'utr_no.min' => 'UTR number must be at least 10 characters long.',
            'utr_no.max' => 'UTR number must not exceed 20 characters.',

            'request_image.required' => 'Request Image is required.',
            'request_image.url' => 'Please upload valid file.',
            'request_image.mimes' => 'Accepted only : jpg,jpeg and png.',
            'request_image.max' => 'Max file size is 2MB',

            'remark.regex' => 'Remark is Invalid.',
            'remark.max' => 'Remark should not exceed 300 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();

            if ($request->hasFile('request_image')) {
                $requestImage = FileUpload::uploadFile($request->request_image, "request_image/$userId", null);
            }

            $data = [
                'user_id' => $userId,
                'request_id' => CommonHelper::getRandomString('REQ', true),
                'amount' => $request->amount,
                'utr_no' => $request->utr_no,
                'image_url' => $requestImage ?? '',
                'request_time' => now(),
                // 'remark' => $request->remark,
                'updated_by' => $userId,
            ];

            $request = LoadMoneyRequest::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Request added Successfully '
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendForgetMpinOtp()
    {
        DB::beginTransaction();
        try {

            $id = Auth::id();
            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $otp = rand(1000, 9999);
            $expiresAt = Carbon::now()->addMinutes(10);

            $user->forget_mpin_otp =  $otp;
            $user->mpin_otp_expires_at =  $expiresAt;
            $user->save();

            try {
                SendingMail::sendForgetPasswordMail([
                    'name' => $user->name,
                    'email' => $user->email,
                    'otp' => $otp,
                    'subject' => 'Forget MPIN',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Mail sending failed: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send OTP. Please try again.',
                ], 500);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your Email adress, Please verify OTP'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function verifyOtpForgetMpin(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
        ]);

        DB::beginTransaction();
        try {

            $id = Auth::id();
            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (!$user->forget_mpin_otp || $user->forget_mpin_otp != $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP',
                ], 401);
            }

            if (Carbon::now()->greaterThan($user->mpin_otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'OTP expired',
                ], 401);
            }

            $user->forget_mpin_otp = NULL;
            $user->mpin_otp_expires_at = NULL;
            $user->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'OTP Verified Successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Verify OTP Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ], 500);
        }
    }


    public function forgetMPIN(Request $request)
    {
        $request->validate([
            'newMpin' => 'required|digits:4',
            'confirmMpin' => 'required|same:newMpin',
        ]);

        DB::beginTransaction();
        try {

            $id = Auth::id();
            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->mpin = Hash::make($request->newMpin);
            $user->save();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'MPIN Updated Successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Verify OTP Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allAgreement()
    {
        $agreements = Agreement::where('status', '1')->latest()->get();
        return view('Agreement.index', compact('agreements'));
    }

    public function userMaintenanceMode()
    {
        return view('Maintenance.maintenance');
    }
}
