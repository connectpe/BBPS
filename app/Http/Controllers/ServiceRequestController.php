<?php

namespace App\Http\Controllers;

use App\Models\UserService;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * List all user services
     */
    public function index()
    {
        $requests = UserService::with(['user', 'service'])
            ->latest()
            ->get();

        $requests = ServiceRequest::with(['user', 'service'])->latest()->get();
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

        $alreadyRequested = UserService::where('user_id', auth()->id())
            ->where('service_id', $request->service_id)
            ->exists();

        if ($alreadyRequested) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service already requested'
                ]);
        try {
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
                'message' => 'Service request sent successfully'

          
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
    public function approve($id)
    {
        $service = UserService::findOrFail($id);

        if ($service->status === 'approved') {
            $service->status = 'pending';
            $service->is_active = '0';
        } else {
            $service->status = 'approved';
            $service->is_active = '1';
        if (! auth()->check() || auth()->user()->role_id !== '1') {
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
        if (! auth()->check() || auth()->user()->role_id !== '1') {
            abort(403, 'Unauthorized action');
        }

        $service->save();

        return back()->with('success', 'Service status updated successfully');
    }
}
