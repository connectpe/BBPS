<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Helpers\CommonHelper;
use App\Models\BusinessCategory;
use App\Models\BusinessInfo;
use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\User;
use App\Models\UsersBank;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;


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
            }
            $data['activeService'] = GlobalService::where(['is_active' => '1'])
                ->select('id', 'slug', 'service_name')
                ->get();

            $data['userdata'] = User::where('id', $userId)->select('name', 'email', 'mobile', 'status', 'role_id', 'profile_image')->first();
            $data['businessInfo'] = BusinessInfo::where('user_id', $userId)->first();
            $data['businessCategory'] = BusinessCategory::where('status', 1)->orderBy('id', 'desc')->get();

            $data['usersBank'] = UsersBank::where('user_id', $userId)->first();

            return view('Admin.profile')->with($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $data['activeService'] = GlobalService::where(['is_active' => '1'])
            ->select('id', 'slug', 'service_name')
            ->get();

        // dd($data);
        return view('Admin.profile')->with($data);
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function disableUserService(Request $request)
    {

        try {

            if (! auth()->check() && auth::user()->role_id != '1') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|string|max:50',
                'type' => 'required|string|in:is_api_allowed,is_active',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = GlobalService::find($request->service_id);

            if (! $data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found.',
                ]);
            }

            switch ($request->type) {
                case 'is_api_allowed':
                    $data->is_activation_allowed = $data->is_activation_allowed == '1' ? '0' : '1';
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'API Activation Updated  Successfully',
                    ]);
                    break;
                case 'is_active':
                    $data->is_active = $data->is_active == '1' ? '0' : '1';
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

    public function AddService(Request $request)
    {
        try {

            if (! auth()->check() || auth()->user()->role_id != '1') {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $request->validate([
                'service_name' => 'required|string|max:50',
            ]);
            $slug = Str::slug($request->service_name);
            $service = GlobalService::create([
                'user_id' => auth()->id(),
                'service_name' => $request->service_name,
                'slug' => $slug,
                'is_active' => 1,
                'is_activation_allowed' => 1,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Service added successfully',
                'data' => $service,
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function EditService(Request $request, $serviceId)
    {
        try {

            if (! auth()->check() || auth()->user()->role_id != 1) {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $request->validate([
                'service_name' => 'required|string|max:50',
            ]);
            $slug = Str::slug($request->service_name);
            $service = GlobalService::where('id', $serviceId)->first();


            $slug = Str::strtolower($request->service_name);

            $service = GlobalService::where('id', $serviceId)->first();
            if (!$service) {


                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }
            $service->service_name = $request->service_name;
            $service->slug = $slug;
            $service->updated_at = now();
            $service->save();

            return response()->json([
                'status' => true,

                'message' => 'Service name updated successfully',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateUser()
    {
        if (! auth()->check() && auth::user()->role_id == '1') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function UserStatusChange(Request $request, $userId)
    {

        try {

            $this->validateUser();

            $request->validate([
                'status' => 'required|in:0,1,2,3,4',
            ]);

            $userId = decrypt($userId);
            $user = User::where('id', $userId)->first();

            if (! $user) {

                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ]);
            }

            $user->status = $request->status;
            $user->updated_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User status updated  successfully',

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function changeUserStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:users,id',
            'status' => 'required|in:0,1,2,3,4'
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->id);

            $user->status = $request->status;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status. Please try again.'
            ], 500);
        }
    }
}
