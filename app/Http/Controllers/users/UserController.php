<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OauthUser;
use Illuminate\Support\Facades\DB;
use App\Models\GlobalService;

use Illuminate\Support\Facades\Auth;
use App\Models\BusinessInfo;
use App\Models\BusinessCategory;
use App\Models\UsersBank;   
use App\Models\User;
use Exception;
use App\Policies\IsUser;




class UserController extends Controller
{

    public function bbpsUsers()

    {
        // $data = $this->isUser(Auth::user());
        // dd($data);
        return view('Users.users');
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


        if (!empty($request->name)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['name']), strtolower($request->name)));
        }
        if (!empty($request->email)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['email']), strtolower($request->email)));
        }
        if (!empty($request->status)) {
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


    public function completeProfile(Request $request,$userId)
    {
        DB::beginTransaction();
       
        try {
          
            $request->validate([              
                'profile_image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'business_name'      => 'required|string|max:255',
                'business_type'      => 'required|string|max:255',
                'industry'           => 'nullable|string|max:255',
                'cin_number'         => 'nullable|string|max:50',
                'gst_number'         => 'required|string|max:50',
                'business_pan'       => 'required|string|max:50',
                'business_email'     => 'nullable|email|max:255',
                'business_phone'     => 'nullable|string|max:20',
                'business_docs.*'    => 'nullable|file|mimes:pdf,jpg,png|max:5120',

              
                'state'              => 'required|string|max:255',
                'city'               => 'required|string|max:255',
                'pincode'            => 'required|string|max:10',
                'business_address'   => 'required|string|max:500',

                'adhar_number'       => 'required|string|max:20',
                'pan_number'         => 'required|string|max:20',
                'adhar_front_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'adhar_back_image'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'pan_card_image'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

               
                'account_holder_name' => 'required|string|max:255',
                'account_number'     => 'required|string|max:30',
                'ifsc_code'          => 'required|string|max:20',
                'branch_name'        => 'required|string|max:255',
                'bank_docs.*'        => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            ]);

            

           
            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_pictures', 'public');

                
                User::where('id', $userId)->update(['profile_image' => $profilePicPath]);
            }

           
            $businessDocsPath = null;
            if ($request->hasFile('business_docs')) {
                $businessDocs = [];
                foreach ($request->file('business_docs') as $doc) {
                    $businessDocs[] = $doc->store('business_documents', 'public');
                }
                $businessDocsPath = json_encode($businessDocs);
            }
            

           
            $adharFrontPath = null;
            if ($request->hasFile('adhar_front_image')) {
                $adharFrontPath = $request->file('adhar_front_image')->store('kyc_documents', 'public');
            }
            

          
            $adharBackPath = null;
            if ($request->hasFile('adhar_back_image')) {
                $adharBackPath = $request->file('adhar_back_image')->store('kyc_documents', 'public');
            }

           
            $panCardPath = null;
            if ($request->hasFile('pan_card_image')) {
                $panCardPath = $request->file('pan_card_image')->store('kyc_documents', 'public');
            }

            $bankDocsPath = null;
            if ($request->hasFile('bank_docs')) {
                $bankDocs = [];
                foreach ($request->file('bank_docs') as $doc) {
                    $bankDocs[] = $doc->store('bank_documents', 'public');
                }
                $bankDocsPath = json_encode($bankDocs);
            }

           
            $categoryId = null;
            if ($request->filled('business_type')) {
                $category = BusinessCategory::where('slug', $request->business_type)
                    ->orWhere('name', $request->business_type)
                    ->first();
                $categoryId = $category?->id;
            }


            $businessInfo = BusinessInfo::create([
                'user_id'                => $userId,
                'business_category_id'   => $categoryId,
                'business_name'          => $request->business_name,
                'industry'               => $request->industry,
                'cin_number'             => $request->cin_number,
                'gst_number'             => $request->gst_number,
                'business_pan_number'    => $request->business_pan,
                'business_email'         => $request->business_email,
                'business_phone'         => $request->business_phone,
                'business_type'          => $request->business_type,
                'aadhar_number'          => $request->adhar_number,
               
                'pan_number'             => $request->pan_number,
                'address'                => $request->business_address,
                'city'                   => $request->city,
                'state'                  => $request->state,
                'pincode'                => $request->pincode,
                              
                'business_document'          => $businessDocsPath,
                'aadhar_front_image'      => $adharFrontPath,
                'aadhar_back_image'       => $adharBackPath,
                'pancard_image'         => $panCardPath,
                
            ]);

           
           
            UsersBank::create([
                'user_id'            => $userId,
                'business_info_id'   => $businessInfo->id,
                'benificiary_name'   => $request->account_holder_name,
                'branch_name'          => $request->branch_name,
                'account_number'     => $request->account_number,
                'ifsc_code'          => $request->ifsc_code,
                'bank_docs'          => $bankDocsPath,
            ]);


            DB::commit();

            $user = User::find($userId);

            return response()->json([
                'status'  => true,
                'message' => 'Profile completed successfully',
                'user'    => $user,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateClientCredentials(Request $request)
    {

        if (!auth()->check() && auth::user()->role_id != '2') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'service' => 'required|string|max:50',
        ]);

        // dd(auth()->id());

        DB::beginTransaction();

        $service = GlobalService::where('slug', $request->service)->select('id')->first();
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ], 404);
        }
        // dd($service);

        try {

            $userId = auth()->id();

            $clientId = 'RAFI' . strtoupper($request->service) . '_' . Str::random(16);
            $clientSecret = hash('sha256', Str::random(32) . now());
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
                'user_id'       => $userId,
                'service_id'     => $service->id,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'is_active'    => '1',
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Client credentials generated successfully',
                'data'    => [
                    'client_id'     => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                ],
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Client credential generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while generating credentials',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function viewSingleUsers()
    {
        return view('Users.view-user');
    }
}
