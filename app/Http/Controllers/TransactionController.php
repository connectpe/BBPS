<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalService;
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
        $priorities = ['low', 'normal', 'high'];
        $categories = ['transaction', 'refund', 'service', 'other'];
        return view('Transaction.transaction-complaint', compact('services','complaints','priorities','categories'));
        // return view('Transaction.transaction-complaint');
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|max:50',
            'category' => 'nullable|string|max:255',
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
        return response()->json(['message' => 'Complaint registered successfully!','complaint' => $complaint,'complaints' => $complaints,]);
    }

    public function complaintStatus()
    {
        return view('Transaction.complaint-status');
    }

    public function transaction_Report()
    {
        return view('Transaction.transaction-report');
    }
}
