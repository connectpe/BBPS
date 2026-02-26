<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    /**
     * List all user services
     */
    public function index()
    {
        try {
            $users = User::select('id', 'name', 'email')->where('role_id', '!=', '1')->where('role_id', '!=', '4')->where('status', '!=', '0')->orderBy('id', 'desc')->get();
            $globalServices = GlobalService::select('id', 'service_name')->where('is_active', '1')->orderBy('id', 'desc')->get();
            return view('Service.request-services', compact('users', 'globalServices'));
        } catch (\Throwable $e) {
            \Log::error('Service Request Index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Store user service request
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'service_id' => 'required|exists:global_services,id',
            ]);

            $userId = auth()->id();
            $alreadyRequested = UserService::where('user_id', auth()->id())
                ->where('service_id', $request->service_id)
                ->exists();

            if ($alreadyRequested) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service already requested',
                    ]);
                }

                return back()->with('error', 'Service already requested');
            }
            $alreadyRequestedRequest = ServiceRequest::where('user_id', auth()->id())
                ->where('service_id', $request->service_id)
                ->exists();

            if ($alreadyRequestedRequest) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service already requested',
                    ]);
                }

                return back()->with('error', 'Service already requested');
            }

            $alreadyRequestedRequest = ServiceRequest::where('user_id', auth()->id())
                ->where('service_id', $request->service_id)
                ->exists();

            if ($alreadyRequestedRequest) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service already requested',
                    ]);
                }

                return back()->with('error', 'Service already requested');
            }

            UserService::create([
                'user_id' => auth()->id(),
                'service_id' => $request->service_id,
                'status' => 'pending',
                'is_api_enable' => '1',
                'is_active' => '1',
            ]);

            Cache::store('redis')->forget("profile:{$userId}:UserServices");
            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service request sent successfully',
                ]);
            }

            return back()->with('success', 'Service request sent successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve / Unapprove service
     */
    public function approveRejectRequestService(Request $request)
    {

        $request->validate([
            'serviceId' => 'required|exists:user_services,id',
        ]);

        DB::beginTransaction();
        try {
            $service = UserService::findOrFail($request->serviceId);
            $userId = $service->user_id;

            if (! $service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Request Service not found',
                ]);
            }

            $service->status = 'approved';
            $service->save();
            Cache::store('redis')->forget("profile:{$userId}:UserServices");


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Service Request Approved Successfully',
            ]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function enabledServices()
    {

        DB::beginTransaction();
        try {
            $userId = Auth::user()->id;
            $services = UserService::select('id', 'service_id')->with('service:id,service_name')->where('user_id', $userId)->where('is_active', '1')->orderBy('id', 'desc')->get();
            DB::commit();

            return view('Service.enabled-services', compact('services'));
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }
}
