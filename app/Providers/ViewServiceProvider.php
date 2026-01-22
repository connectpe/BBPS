<?php

namespace App\Providers;

use App\Models\GlobalService;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $services = GlobalService::where('is_active', '1')->get();

            if (Auth::check() && Auth::user()->status == '1') {
                $requestedServices = ServiceRequest::latest()
                    ->get()
                    ->unique('service_id')
                    ->keyBy('service_id');
            } else {
                $requestedServices = ServiceRequest::where('user_id', Auth::id())
                    ->get()
                    ->keyBy('service_id');
            }

            $view->with(compact('services', 'requestedServices'));
        });
    }
}
