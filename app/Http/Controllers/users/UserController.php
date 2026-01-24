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
                
                'business_name'     => 'required|string',
                'business_pan'      => 'required|string',
                'business_type'     => 'required|string',

                'aadhar_name'       => 'required|string',
                'aadhar_number'     => 'required|string',
                'gst_number'        => 'required|string',

                'pan_owner_name'    => 'required|string',
                'pan_number'        => 'required|string',

                'address'           => 'required|string',
                'city'              => 'required|string',
                'state'             => 'required|string',
                'pincode'           => 'required|string',

                'baneficiary_name'  => 'required|string',
                'bank_name'         => 'required|string',
                'account_number'    => 'required|string',
                'ifsc_code'         => 'required|string',
            ]);

            $categoryId = null;
            if ($request->filled('business_type')) {
                $category = BusinessCategory::where('slug', $request->business_type)->first();
                $categoryId = $category?->id;
            }


            $businessInfo = BusinessInfo::create([
                'user_id'                => $userId,
                'business_category_id'   => $categoryId,
                'business_name'          => $request->business_name,
                'business_pan_number'    => $request->business_pan,
                'business_pan_name'      => $request->pan_owner_name,
                'aadhar_name'            => $request->aadhar_name,
                'aadhar_number'          => $request->aadhar_number,
                'gst_number'             => $request->gst_number,
                'pan_owner_name'         => $request->pan_owner_name,
                'pan_number'             => $request->pan_number,
                'address'                => $request->address,
                'city'                   => $request->city,
                'state'                  => $request->state,
                'pincode'                => $request->pincode,
            ]);


            UsersBank::create([
                'user_id'            => $userId,
                'business_info_id'   => $businessInfo->id,
                'baneficiary_name'   => $request->baneficiary_name,
                'bank_name'          => $request->bank_name,
                'account_number'     => $request->account_number,
                'ifsc_code'          => $request->ifsc_code,
            ]);


            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Profile completed successfully',
                'user'    => $user,
            ], 201);
        } catch (\Exception $e) {


            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateClientCredentials(Request $request)
    {

        if(!auth()->check() && auth::user()->role_id != '2'){
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

