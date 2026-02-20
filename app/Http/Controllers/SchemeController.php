<?php

namespace App\Http\Controllers;

use App\Models\GlobalService;
use App\Models\Scheme;
use App\Models\User;
use App\Models\UserConfig;
use Illuminate\Support\Facades\DB;

class SchemeController extends Controller
{
    public function index()
    {
        DB::beginTransaction();
        try {
            $globalServices = GlobalService::select('id', 'service_name')->where('is_active', '1')->orderBy('id', 'desc')->get();
            $schemes = Scheme::select('id', 'scheme_name')->orderBy('id', 'desc')->get();
            $users = User::select('id', 'name')->with('business:id,user_id,business_name')->get();
            $relations = UserConfig::with(['user:id,name,email', 'scheme:id,scheme_name'])->orderBy('id', 'desc')->get();
            $assignedUsers = $relations->pluck('user')->unique('id')->filter();
            $assignedSchemes = $relations->pluck('scheme')->unique('id')->filter();
            DB::commit();
            return view('scheme.index', compact('globalServices', 'schemes', 'users', 'relations', 'assignedUsers', 'assignedSchemes'));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }
}
