<?php

namespace App\Providers;

use App\Models\GlobalService;
use App\Models\User;
use App\Models\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer(['layouts.header', 'Admin.profile'], function ($view) {

            $services = GlobalService::where('is_active', '1')->get();

            $requestedServices = collect();
            $businessWallet = 0;

            if (Auth::check()) {

                if (Auth::user()->status == '1') {
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

                if (Auth::check()) {

                    if (Auth::user()->role_id == 1) {
                        $businessWallet = User::sum('transaction_amount') ?? 0;
                    } else {
                        $businessWallet = User::where('id', Auth::id())
                            ->value('transaction_amount') ?? 0;
                    }
                }
            }

            $view->with(compact('services', 'requestedServices', 'businessWallet'));
        });
    }
}
