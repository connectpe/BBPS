<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplainReportController extends Controller
{
    public function complainReport()
    {
        // Admin page: show all complaints (latest first)
        $complaints = Complaint::with('user')->latest()->get();
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['Low', 'Normal', 'High'];

        return view('ComplainReport.index', compact('complaints', 'statuses', 'priorities'));
    }

    public function updateComplaint(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'admin_notes' => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);

        $complaint->status = $request->status;
        $complaint->admin_notes = $request->admin_notes;
        if ($request->status === 'resolved') {
            $complaint->resolved_at = now();
        } else {
            $complaint->resolved_at = null;
        }

        $complaint->save();

        return response()->json(['message' => 'Complaint updated successfully!']);
    }
}
