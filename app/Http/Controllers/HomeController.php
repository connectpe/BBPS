<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {

        try {
            $services = GlobalService::where('is_active', '1')->get();
            $requestedServices = ServiceRequest::where('user_id', auth()->id())
                ->get()
                ->keyBy('service_id');

            return view('Dashboard.dashboard', compact('services', 'requestedServices'));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function loginRedirect()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('Front.user-register');
    }

    public function apiPartner()
    {
        return view('Dashboard.api-dashboard');
    }

    public function supportdashboard()
    {
        return view('Dashboard.support-dashboard');
    }
}
