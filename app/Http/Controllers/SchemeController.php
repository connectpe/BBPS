<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\Scheme;
use App\Models\User;
use App\Models\UserConfig;

class SchemeController extends Controller
{
    public function index()
    {
        $globalServices = GlobalService::where('is_active', '1')->orderBy('id', 'desc')->get();
        $schemes = Scheme::orderBy('id', 'desc')->get();
        $users = User::all();
        $relations = UserConfig::with(['user', 'scheme'])->orderBy('id', 'desc')->get();
        $assignedUsers = $relations->pluck('user')->unique('id')->filter();
        $assignedSchemes = $relations->pluck('scheme')->unique('id')->filter();
        return view('scheme.index', compact('globalServices','schemes', 'users', 'relations','assignedUsers','assignedSchemes'));
    }
}
