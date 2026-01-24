<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
       
        $services = GlobalService::where('is_active', '1')->get();
        $requestedServices = ServiceRequest::where('user_id', auth()->id())
            ->get()
            ->keyBy('service_id'); 

        return view('dashboard', compact('services', 'requestedServices'));
    }
}
