<?php

namespace App\Http\Controllers\users;

use App\Facades\FileUpload;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessInfo;
use App\Models\GlobalService;
use App\Models\IpWhitelist;
use App\Models\OauthUser;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserRooting;
use App\Models\UsersBank;
use App\Models\UserService;
use App\Models\WebHookUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function bbpsUsers()
    {
        $users = User::where('role_id', '!=', '1')->where('status', '!=', '0')->orderBy('id', 'desc')->get();

        return view('Users.users', compact('users'));
    }
    public function redirectToKycPage(){
        return view('Users.kyc-page');
    }
    public function redirectTounauthrized(){
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
            $users = array_filter($users, fn ($u) => str_contains(strtolower($u['name']), strtolower($request->name)));
        }
        if (! empty($request->email)) {
            $users = array_filter($users, fn ($u) => str_contains(strtolower($u['email']), strtolower($request->email)));
        }
        if (! empty($request->status)) {
            $users = array_filter($users, fn ($u) => $u['status'] == $request->status);
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

            $validator = Validator::make(
                $request->all(),
                [
                    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'business_name' => 'required|string|max:255',
                    'business_category' => 'required|exists:business_categories,id',
                    'business_type' => 'required|string|max:255',

                    // 'cin_number'         => 'nullable|string|max:50|unique:business_infos,cin_no',
                    // 'gst_number'         => 'required|string|max:50|unique:business_infos,gst_number',
                    // 'business_pan'       => 'required|string|max:50|unique:business_infos,business_pan_number',
                    // 'business_email'     => 'nullable|email|max:255|unique:business_infos,business_email ',
                    // 'business_phone'     => 'nullable|string|max:20|unique:business_infos,business_phone ',

                    'cin_number' => 'nullable|string|max:50|unique:business_infos,cin_no,'.($businessData->id ?? 'NULL').',id|regex:/^[A-Z]{1}[0-9]{5}[A-Z]{2}[0-9]{4}[A-Z]{3}[0-9]{6}$/i',
                    'gst_number' => 'required|string|max:50|unique:business_infos,gst_number,'.($businessData->id ?? 'NULL').',id|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                    'business_pan' => 'required|string|max:50|unique:business_infos,business_pan_number,'.($businessData->id ?? 'NULL').',id|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i',
                    'business_email' => 'nullable|email|max:255|unique:business_infos,business_email,'.($businessData->id ?? 'NULL').',id',
                    'business_phone' => 'nullable|string|max:20|unique:business_infos,business_phone,'.($businessData->id ?? 'NULL').',id|regex:/^[6-9]\d{9}$/',

                    'business_docs.*' => 'nullable|file|mimes:pdf,jpg,png|max:5120',

                    'state' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'pincode' => 'required|string|max:10',
                    'business_address' => 'required|string|max:500',

                    'adhar_number' => 'required|string|max:20|regex:/^\d{12}$/|unique:business_infos,aadhar_number,'.($businessData->id ?? 'NULL').',id',
                    'pan_number' => 'required|string|max:20|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i|unique:business_infos,pan_number,'.($businessData->id ?? 'NULL').',id',
                    'adhar_front_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'adhar_back_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'pan_card_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                    'account_holder_name' => 'required|string|max:255',
                    'account_number' => 'required|string|max:30|unique:users_banks,account_number,'.($bankDetail->id ?? 'NULL').',id',
                    'ifsc_code' => 'required|string|max:20',
                    'branch_name' => 'required|string|max:255',
                    'bank_docs' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
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

                    'business_docs.*.file' => 'Each business document must be a valid file.',
                    'business_docs.*.mimes' => 'Business documents must be a file of type: pdf, jpg, png.',
                    'business_docs.*.max' => 'Business documents must not exceed 5MB each.',

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

            $businessDocsPath = null;
            if ($request->hasFile('business_docs')) {
                $oldDoc = $businessData?->business_document ? json_decode($businessData?->business_document, true) : null;
                $businessDocs = FileUpload::uploadFile($request->business_docs, "business_documents/$userId", $oldDoc);
                $businessDocsPath = json_encode($businessDocs);
            }
            // dd($businessDocsPath);
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

            $data = [
                'business_name' => $request->business_name,
                'industry' => $request->industry,
                'cin_no' => $request->cin_number,
                'gst_number' => $request->gst_number,
                'business_pan_number' => $request->business_pan,
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_type' => $request->business_type,
                'aadhar_number' => $request->adhar_number,

                'pan_number' => $request->pan_number,
                'address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,

                'aadhar_front_image' => $adharFrontPath,
                'aadhar_back_image' => $adharBackPath,
                'pancard_image' => $panCardPath,

            ];

            if ($businessDocsPath != null) {
                $data['business_document'] = $businessDocsPath;
            }

            $businessInfo = BusinessInfo::updateOrCreate([
                'user_id' => $userId,
                'business_category_id' => $request->business_category,
            ], $data);

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
                'message' => 'Error: '.$e->getMessage(),
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
        $isEnableService = UserService::where('user_id',$userId)->where('service_id',$service->id)->where('status','approved')->where('is_active','1')->first();
        if(!$isEnableService){
            return response()->json([
                'status' => false,
                'message' => $request->service. 'is not enable or approved by the admin',
            ], 401);
        }
        // dd($service);

        try {


            $userId = auth()->id();
            $clientId = 'RAFI' . strtoupper($request->service) . '_' . Str::random(16);
            $plainSecret = Str::random(32);
            $encryptedSecret = encrypt($plainSecret);

            $secretCount = OauthUser::where('user_id', $userId)
                ->where('service_id', $service->id)
                ->count();

            if ($secretCount > 1) {
                // If existing credentials found, deactivate them
                OauthUser::where('user_id', $userId)
                    ->where('service_id', $service->id)
                    ->update(['is_active' => '0']);
            }

            $credential = OauthUser::create([
                'user_id' => $userId,
                'service_id' => $service->id,
                'client_id' => $clientId,
                'client_secret' => $encryptedSecret,
                'is_active' => '1',
            ]);

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
            CommonHelper::checkAuthUser();
            $userId = $Id;

            $data['userData'] = User::where('id', $userId)
                ->select('id', 'name', 'email', 'mobile', 'status', 'role_id')
                ->firstOrFail();

            $data['businessInfo'] = BusinessInfo::where('user_id', $userId)->first();
            $data['usersBank'] = UsersBank::where('user_id', $userId)->first();

            $data['serviceEnabled'] = UserService::where('user_id', $userId)->where('status', 'approved')->get();
            $data['serviceRequest'] = UserService::where('user_id', $userId)->where('status', 'pending')->get();

            $data['globalServices'] = GlobalService::where('is_active', '1')
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
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getServiceProviders(Request $request, $serviceId)
    {
        try {
            $providers = Provider::where('service_id', $serviceId)
                ->select('id', 'provider_name as name', 'provider_slug as slug')
                ->get();

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
                'message' => 'Error fetching providers: '.$e->getMessage(),
            ], 500);
        }
    }

    public function ApiLog()
    {

        $users = User::where('role_id', '!=', '1')->where('status', '!=', '0')->orderBy('id', 'desc')->get();

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

            if($ipCount >= 5) {
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
                'message' => 'System Error: '.$e->getMessage(),
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
            $duplicate = IpWhitelist::where('user_id', $userId)
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
                'message' => 'Error: '.$e->getMessage(),
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
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function generateMpin(Request $request)
    {
        try {

            if (Auth::user()->role_id != '2') {
                return response()->json([
                    'status' => false,
                    'message' => 'You are unauthorized',
                ], 403);
            }

            $request->validate([
                'current_mpin' => 'required',
                'new_mpin' => 'required|min:4|max:10',
                'confirm_mpin' => 'required|same:new_mpin',
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

            return response()->json([
                'status' => true,
                'message' => 'MPIN updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addWebHookUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ], [
            'url.required' => 'url is required.',
            'url.url' => 'Please enter a valid url.',
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
            $urlCount = WebHookUrl::where('user_id', $userId)->count();

            if ($urlCount >= 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Url already added.',
                ]);
            }

            $data = [
                'user_id' => $userId,
                'url' => $request->url,
                'updated_by' => $userId,
            ];

            WebHookUrl::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Url Added Successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editWebHookUrl(Request $request, $Id)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ], [
            'url.required' => 'url is required.',
            'url.url' => 'Please enter a valid url.',
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
            $url = WebHookUrl::find($Id);
            $urlCount = WebHookUrl::where('user_id', $userId)->where('id', '!=', $Id)->count();

            if ($urlCount >= 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Url already added.',
                ]);
            }

            if (!$url) {
                return response()->json([
                    'status' => false,
                    'message' => 'Url not Found.',
                ]);
            }

            if ($userId != $url->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Url Updating.',
                ]);
            }

            $data = [
                'url' => $request->url,
                'updated_by' => $userId,
            ];

            $url->update($data);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Url Updated Successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function WebHookUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ], [
            'url.required' => 'Url is required.',
            'url.url' => 'Please enter a valid url.',
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

            $webhook = WebHookUrl::updateOrCreate(
                ['user_id' => $userId],
                ['url' => $request->url, 'updated_by' => $userId]
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $webhook->wasRecentlyCreated
                    ? 'Url Added Successfully.'
                    : 'Url Updated Successfully.',
                'data' => [
                    'url' => $webhook->url,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

}
