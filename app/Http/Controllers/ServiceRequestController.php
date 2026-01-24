<?php

namespace App\Http\Controllers;

use App\Models\UserService;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * List all service requests
     */
    public function index()
    {
        $requests = ServiceRequest::with(['user', 'service'])
            ->latest()
            ->get();

        return view('Service.request-services', compact('requests'));
    }

    /**
     * Store user service request
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:global_services,id',
        ]);

        $alreadyRequested = ServiceRequest::where('user_id', auth()->id())
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

        try {
            // Create service request
            ServiceRequest::create([
                'user_id'    => auth()->id(),
                'service_id' => $request->service_id,
                'status'     => 'pending',
            ]);

            // Create user service
            UserService::create([
                'user_id'       => auth()->id(),
                'service_id'    => $request->service_id,
                'status'        => 'pending',
                'is_api_enable' => 1,
                'is_active'     => 0,
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
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve service request (ADMIN)
     */
    public function approve($id)
    {
        if (!auth()->check() || auth()->user()->role_id != 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);
        $userService = UserService::where('user_id', $serviceRequest->user_id)
            ->where('service_id', $serviceRequest->service_id)
            ->first();

        $serviceRequest->update([
            'status' => 'approved',
        ]);

        if ($userService) {
            $userService->update([
                'status'    => 'approved',
                'is_active' => 1,
            ]);
        }

        return back()->with('success', 'Service activated successfully');
    }

    /**
     * Reject service request (ADMIN)
     */
    public function reject($id)
    {
        if (!auth()->check() || auth()->user()->role_id != 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->update([
            'status' => 'rejected',
        ]);

        UserService::where('user_id', $serviceRequest->user_id)
            ->where('service_id', $serviceRequest->service_id)
            ->update([
                'status'    => 'rejected',
                'is_active' => 0,
            ]);

        return back()->with('success', 'Service request rejected');
    }
}
