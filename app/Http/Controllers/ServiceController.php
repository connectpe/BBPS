<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{

    public function utilityService()
    {
        return view('Service.services');
    }


    public function rechargeService()
    {
        return view('Service.recharge');
    }


    public function bankingService()
    {
        return view('Service.banking');
    }


    public function ourService()
    {
        return view('Service.our-service');
    }

    
}
