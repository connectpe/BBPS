<?php

namespace App\Http\Controllers;

use App\Models\User;

class LadgerController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role_id', ['2', '3'])->where('status', '!=', '0')->orderBy('id', 'desc')->get();
        return view('ladger.index',compact('users'));
    }


    public function reports()
    {
        return view('Reports.reports');
    }
}
