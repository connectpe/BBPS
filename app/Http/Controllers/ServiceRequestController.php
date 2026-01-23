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
    }

    public function approve($id)
    {
        $request = ServiceRequest::findOrFail($id);
        if ($request->status === 'approved') {
            $request->status = 'pending';
        } else {
            $request->status = 'approved';
        }

        $request->save();

        return back()->with('success', 'Service status updated successfully');
    }
}
