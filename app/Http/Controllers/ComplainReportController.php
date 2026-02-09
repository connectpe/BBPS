<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use App\Models\UserAssignedToSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplainReportController extends Controller
{
    public function complainReport()
    {
        $users = [];
        $role = Auth::user()->role_id;
        $priorities = ['low', 'normal', 'high', 'urgent'];

        if ($role == 1) {
            $users = User::where('role_id', '!=', '1')->whereHas('complaints')->where('status', '!=', '0')->orderBy('id', 'desc')->get();
        } elseif ($role == 4) {
            $assignedUser =  UserAssignedToSupport::where('assined_to', Auth::user()->id)->pluck('user_id')->toArray();
            $users = User::whereNotIn('role_id',  [1, 3, 4])->whereHas('complaints')->whereIn('id', $assignedUser)->where('status', '!=', '0')->orderBy('id', 'desc')->get();
        }
       

        return view('ComplainReport.index', compact('priorities', 'users'));
    }



    public function updateComplaint(Request $request, $id)
    {

        $request->validate([
            'status' => 'required|in:Open,In Progress,Closed',
            'remark' => 'required_if:status,Closed|string',
        ]);

        DB::beginTransaction();
        try {

            $complaint = Complaint::findOrFail($id);

            if (!$complaint) {
                return response()->json([
                    'status' => false,
                    'message' => 'Complaint not Found'
                ]);
            }

            $complaint->status = $request->status;
            $complaint->remark = $request->remark;
            $complaint->updated_by = Auth::user()->id;


            if ($request->status === 'Closed') {
                $complaint->resolved_at = now();
            }

            $complaint->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Complaint Updated Successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage()
            ]);
        }
    }
}
