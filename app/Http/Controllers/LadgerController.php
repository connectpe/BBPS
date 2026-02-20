<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LadgerController extends Controller
{
    public function index()
    {

        DB::beginTransaction();
        try {
            $users = User::select('id', 'name', 'email')->whereIn('role_id', ['2', '3'])->where('status', '!=', '0')->orderBy('id', 'desc')->get();
            DB::commit();
            return view('ladger.index', compact('users'));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }


    public function reports()
    {
        return view('Reports.reports');
    }
}
