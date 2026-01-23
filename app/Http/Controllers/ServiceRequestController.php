<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Store service request (USER)
     */
    public function index()
    {
        $requests = ServiceRequest::with(['user', 'service'])->latest()->get();

        return view('Service.request-services', compact('requests'));
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
            'service_id' => 'required|exists:global_services,id',
            ]);
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
                'user_id' => auth()->id(),
                'service_id' => $request->service_id,
                'status' => 'pending',
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Service request sent successfully']);
            }

            return back()->with('success', 'Service request sent successfully');

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function approve($id)
    {
        if (! auth()->check() || auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);
        if ($serviceRequest->status === 'approved') {
            return back()->with('info', 'Service already activated');
        }

        $serviceRequest->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'Service activated successfully');
    }

    public function reject($id)
    {
        if (! auth()->check() || auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action');
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        $serviceRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Service request rejected');
    }
}
