<?php

namespace App\Http\Middleware;

use App\Models\Maintenance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = Maintenance::first();

        if (!$maintenance || $maintenance->status == '0') {
            if ($request->is('maintenance-mode*')) {
                // if (Auth::check()) {
                //     Auth::logout(); 
                //     return redirect()->route('home')->with('status', 'Maintenance mode is now OFF. Please login again.');
                // }

                return redirect()->route('home');
            }
            return $next($request); 
        }
        if ($request->is('admin*') || $request->is('login*') || $request->is('logout') || $request->is('maintenance-mode*')) {
            return $next($request);
        }
        return redirect()->route('user_maintenance_mode');
    }
}