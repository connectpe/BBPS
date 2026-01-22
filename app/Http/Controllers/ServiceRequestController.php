<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Store service request (USER)
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:global_services,id',
        ]);

        // Prevent duplicate request for same service
        $alreadyRequested = ServiceRequest::where('user_id', auth()->id())
            ->where('service_id', $request->service_id)
            ->exists();

        if ($alreadyRequested) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Service already requested']);
            }
            return back()->with('error', 'Service already requested');
        }

        ServiceRequest::create([
            'user_id'    => auth()->id(),
            'service_id' => $request->service_id,
            'status'     => 'pending',
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Service request sent successfully']);
        }
        return back()->with('success', 'Service request sent successfully');
    }

    /**
     * Approve service request (ADMIN)
     */
    public function approve($id)
    {
        // Authorization check (Admin only)
        if (!auth()->check() || auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        // Prevent re-approving
        if ($serviceRequest->status === 'approved') {
            return back()->with('info', 'Service already activated');
        }

        $serviceRequest->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'Service activated successfully');
    }

    /**
     * Reject service request (ADMIN - optional)
     */
    public function reject($id)
    {
        if (!auth()->check() || auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Service request rejected');
    }
}
