<?php

namespace App\Providers;

use App\Models\GlobalService;
use App\Models\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.header', function ($view) {

            $services = GlobalService::where('is_active', '1')->get();

            if (Auth::check() && Auth::user()->status == '1') {
                $requestedServices = UserService::latest()
                    ->where('user_id', Auth::id())
                    ->where('is_active', '1')
                    ->get()
                    ->unique('service_id')
                    ->keyBy('service_id');

            } else {
                $requestedServices = UserService::where('user_id', Auth::id())
                    ->get()
                    ->keyBy('service_id');
            }

            $view->with(compact('services', 'requestedServices'));
        });
    }
}
