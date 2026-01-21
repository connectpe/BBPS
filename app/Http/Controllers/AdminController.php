<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalService;

class AdminController extends Controller
{
    public function adminProfile()
    {
        $data['activeService'] = GlobalService::where(['is_active' => '1'])
            ->select('id', 'slug', 'service_name')
            ->get();
        // dd($data);
        return view('Admin.profile')->with($data);
    }
}
