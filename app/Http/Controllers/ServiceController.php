<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\User;
use App\Models\UserService;
use App\Models\BusinessInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function utilityService()
    {
        return view('Service.services');
    }

    public function rechargeService()
    {
        return view('Service.recharge');
    }

    public function bankingService()
    {
        return view('Service.banking');
    }

    public function ourService()
    {
        DB::beginTransaction();
        try {
            $globalServices = GlobalService::select('id', 'service_name')->where('is_active', '1')->orderBy('id', 'desc')->get();
            $users = User::select('id', 'name', 'email')->whereNotIN('role_id', ['1', '4'])->where('status', '!=', '0')->orderBy('id', 'desc')->get();
            DB::commit();

            return view('Service.our-service', compact('users', 'globalServices'));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : '.$e->getMessage(),
            ]);
        }
    }

    public function activeUserService(Request $request)
    {

        $request->validate([
            'service_id' => 'required|exists:user_services,id',
            'type' => 'required|in:is_api_enable,is_active',
        ]);

        DB::beginTransaction();
        try {

            $type = $request->type;
            $service = UserService::find($request->service_id);

            if (! $service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ]);
            }

            switch ($type) {
                case 'is_active':
                    $service->is_active = $service->is_active == '1' ? '0' : '1';
                    $service->save();
                    $message = 'Status Changed Successfully';
                    break;
                case 'is_api_enable':
                    $service->is_api_enable = $service->is_api_enable == '1' ? '0' : '1';
                    $service->save();
                    $message = 'API Status Changed Successfully';
                    break;
                default:

                    break;
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $message ?? 'Status Changed Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function apipartnerservices()
    {
        try {
            $services = GlobalService::select('id', 'service_name', 'slug')->where('is_active', '1')->orderBy('id', 'desc')->get();
            $requestedServices = UserService::where('user_id', Auth::id())->select('id', 'user_id', 'service_id', 'status')->get() ->keyBy('service_id'); 
            $business = BusinessInfo::where('user_id', Auth::id())->first();
            $userKycStatus = $business && (string)$business->is_kyc === '1';
            return view('Service.api-partner-services', compact('services', 'requestedServices', 'userKycStatus'));
        } catch (\Throwable $e) {
            Log::error('API Partner Services Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
