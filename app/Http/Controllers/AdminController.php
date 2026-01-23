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
use Illuminate\Support\Facades\Validator;

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


    public function disableUserService(Request $request)
    {


        try {

            if (!auth()->check() && auth::user()->role_id != '1') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|string|max:50',
                'type' => 'required|string|in:is_api_allowed,is_active',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            $data = GlobalService::find($request->service_id);

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found.',
                ]);
            }

            switch ($request->type) {
                case 'is_api_allowed':
                    $data->is_activation_allowed =  $data->is_activation_allowed == '1' ? '0' : '1';
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'API Activation Updated  Successfully',
                    ]);
                    break;
                case 'is_active':
                    $data->is_active =  $data->is_active == '1' ? '0' : '1';
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Service Status Updated  Successfully',
                    ]);
                    break;
                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid type provided',
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
