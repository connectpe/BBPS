<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\GlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function transactionStatus()
    {
        return view('Transaction.transaction-status');
    }

    public function transactionComplaint()
    {
        $services = GlobalService::where('is_active', '1')->orderBy('service_name')->get();
        $complaints = Complaint::where('user_id', auth()->id())->latest()->get();

        // if you want normal first, use this:
        $priorities = ['normal', 'low', 'high'];

        $categories = ['transaction', 'refund', 'service', 'other'];

        return view('Transaction.transaction-complaint', compact(
            'services',
            'complaints',
            'priorities',
            'categories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,normal,high', // fixed
            'category' => 'required|in:transaction,refund,service,other', // make required
            'attachment' => 'nullable|file|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('complaints', 'public');
        }

        do {
            $ref = 'CMP-'.strtoupper(Str::random(10));
        } while (Complaint::where('reference_number', $ref)->exists());

        $complaint = Complaint::create([
            'reference_number' => $ref,
            'user_id' => Auth::id(),
            'service_name' => $request->service_name,
            'description' => $request->description,
            'status' => 'open',
            'priority' => $request->priority,
            'category' => $request->category,
            'attachment_path' => $attachmentPath,
        ]);

        $complaints = Complaint::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'message' => 'Complaint registered successfully!',
            'complaint' => $complaint,
            'complaints' => $complaints,
        ]);
    }

    public function complaintStatus()
    {
        $categories = ['transaction', 'refund', 'service', 'other'];

        return view('Transaction.complaint-status', compact('categories'));
    }

    public function checkComplaintStatus(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string',
        ]);

        $complaint = Complaint::where('reference_number', $request->reference_number)
            ->where('user_id', auth()->id()) // user sirf apni complaint check kare
            ->first();

        if (! $complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found for given Reference Number.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'reference_number' => $complaint->reference_number,
                'service_name' => $complaint->service_name,
                'resolved_at' => $complaint->resolved_at
                    ? $complaint->resolved_at->format('d-m-Y H:i')
                    : '-',
                'complaint_status' => strtoupper($complaint->status),
            ],
        ]);
    }

    public function transaction_Report()
    {
        return view('Transaction.transaction-report');
    }
}
