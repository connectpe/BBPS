<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function adminProfile()
    {
        return view('Admin.profile');
    }
}
