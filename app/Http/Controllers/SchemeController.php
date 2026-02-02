<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\User;
use App\Models\Scheme;

class SchemeController extends Controller
{
    public function index()
    {
        $globalServices = GlobalService::where('is_active', '1')->orderBy('id', 'desc')->get();
        $schemes = Scheme::orderBy('id', 'desc')->get();
        $users = User::all();
        return view('scheme.index', compact('globalServices', 'schemes', 'users'));
    }
}
