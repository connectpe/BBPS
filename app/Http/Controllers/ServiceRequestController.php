<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    /**
     * List all user services
     */
    public function index()
    {
        // $requests = UserService::with(['user', 'service'])
        //     ->latest()
        //     ->get();

        // $requests = UserService::with(['user', 'service'])
        //     ->latest()
        //     ->get();

        $users = User::where('role_id', '!=', '1')->where('status', '!=', '0')->orderBy('id', 'desc')->get();
        $globalServices = GlobalService::where('is_active', '1')->orderBy('id', 'desc')->get();

        return view('Service.request-services', compact('users', 'globalServices'));
    }

    /**
     * Store user service request
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'service_id' => 'required|exists:global_services,id',
            ]);


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


            // 🔹 Check already requested in ServiceRequest
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service request sent successfully',
                ]);
            }

            return back()->with('success', 'Service request sent successfully');
        } catch (\Exception $e) {

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
            'status' => 'required|in:approved,rejected',
        ]);


        DB::beginTransaction();
        try {
            $service = UserService::findOrFail($request->serviceId);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Request Service not found'
                ]);
            }

            $service->status = $request->status;
            $service->save();

            $message =  $request->status == 'approved'  ? 'Approved'  : 'Rejected';

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Service Request $message Successfully"
            ]);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage()
            ]);
        }
    }


    public function enabledServices()
    {
        $userId = Auth::user()->id;
        $services = UserService::with('service')->where('user_id', $userId)->where('is_active', '1')->orderBy('id', 'desc')->get();
        return view('Service.enabled-services', compact('services'));
    }
}
