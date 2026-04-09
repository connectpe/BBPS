<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceCostController extends Controller
{
    public function getServiceCost(Request $request){

        $request = $request->all();
        $user = Auth::user();

        $name = $user->name;
        $mobile = $user->mobile;
        $email = $user->email;
        

    }
}
