<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\GlobalService;
use App\Helpers\CommonHelper;
use App\Models\OauthUser;
use App\Models\User;
use App\Models\BusinessInfo;
use App\Models\BusinessCategory;
use App\Models\UsersBank;
use App\Models\UsersService;



class AdminController extends Controller
{
    public function adminProfile($userId)
    {
        try {
            CommonHelper::checkAuthUser();
            $userId = auth()->id();
            if (auth()->user()->role_id == '2') {
                $data['saltKeys'] = OauthUser::where('user_id', auth()->id())
                    ->where('is_active', '1')
                    ->select('client_id', 'client_secret', 'created_at')
                    ->get();

                // dd($data['saltKeys']);
            }
            $data['activeService'] = GlobalService::where(['is_active' => '1'])
                ->select('id', 'slug', 'service_name')
                ->get();
            $data['userdata'] = User::where('id', $userId)->select('name', 'email', 'mobile', 'status', 'role_id')->first();
            $data['businessInfo'] = BusinessInfo::where('user_id', $userId)->first();
            // $data['businessCategory'] = BusinessCategory::where('id',$businessInfo->business_category_id)->first();

            $data['usersBank'] = UsersBank::where('user_id', $userId)->select('bank_name', 'account_number', 'ifsc_code', 'created_at')->first();

            // dd($data);

            return view('Admin.profile')->with($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function dashboard()
    {
        return view('dashboard');
    }


    public function disableUserService(Request $request){
        try{
            
            if(!auth()->check() && auth::user()->role_id != '1'){
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
           
            
            $request->validate([
                'service_id' => 'required|string|max:50',
                'is_active' => 'required|in:0,1',
                'user_id' => 'required|string|max:50',
                'type' => 'required|string|in:disable,enable',
            ]);
            
            $userId = decrypt($request->user_id);
            // Logic to disable user service goes here
            $data = UsersService::where('user_id', $userId)->string('service_id',$request->service_id)->first();

            if($data->status == '0'){
                return response()->json([
                    'status' => false,
                    'message' => 'Service is not approved yet by the admin',
                ]);
            }
            if(!$data){
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found for user',
                ]);
            }
            switch($request->type){
                case 'is_api_allowed':
                    $data->is_api_enable = $request->is_active;
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Users api status updated  successfully',
                    ]);
                    break;
                case 'is_active':
                    $data->is_active = $request->is_active;
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Users api status updated  successfully',
                    ]);
                    break;
                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid type provided',
                    ]);
            }
            

            

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    

}
