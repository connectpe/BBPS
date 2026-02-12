<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintsCategory;
use App\Models\GlobalService;
use App\Models\Transaction;
use App\Models\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function transactionStatus()
    {
        return view('Transaction.transaction-status');
    }

    public function transactionComplaint()
    {
        $priorities = ['Low', 'Medium', 'High'];
        // $services = GlobalService::where('is_active', '1')->orderBy('id', 'desc')->get();

        $services = UserService::with('service')->where('user_id', Auth::user()->id)->where('status', 'approved')->where('is_active', '1')->orderBy('id', 'desc')->get();
        $categories = ComplaintsCategory::where('status', '1')->orderBy('id', 'desc')->get();

        return view('Transaction.transaction-complaint', compact('services', 'priorities', 'categories'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'service_id' => 'required|exists:global_services,id',
            'description' => 'required|string|min:20|max:500',
            'priority' => 'required|in:Low,High,Medium',
            'category' => 'required|exists:complaints_categories,id',
            'attachment' => 'nullable|file|max:2048|mimes:jpg,png,jpeg',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('complaints', 'public');
        }

        do {
            $ticketId = '#'.strtoupper(rand(000000000000, 111111111111));
        } while (Complaint::where('ticket_number', $ticketId)->exists());

        $userId = Auth::user()->id;

        $data = [
            'ticket_number' => $ticketId,
            'user_id' => $userId,
            'service_id' => $request->service_id,
            'complaints_category' => $request->category,
            'priority' => $request->priority,
            'attachment_file' => $attachmentPath,
            'description' => $request->description,
            'updated_by' => $userId,
        ];

        $complaint = Complaint::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Complaint Registered Successfully',
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
            'ticket_number' => 'required|string',
        ]);

        $complaint = Complaint::where('ticket_number', $request->ticket_number)
            ->where('user_id', auth()->id()) // user sirf apni complaint check kare
            ->first();

        if (! $complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found for given Ticket Number.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'ticket_number' => $complaint->ticket_number,
                'service_name' => $complaint->service?->service_name,
                'resolved_at' => $complaint->resolved_at ? $complaint->resolved_at->format('M-d-Y h:i:s a') : '-',
                'complaint_status' => $complaint->status,
            ],
        ]);
    }

    public function transaction_Report()
    {
        return view('Transaction.transaction-report');
    }

    public function downloadInvoice($id)
    {
        $txn = Transaction::with(['user','user.business'])
            ->where('id', $id)
            ->where('status', 'processed')
            ->firstOrFail();

        $pdf = Pdf::loadView('Users.reports.recharge-transaction-invoice', compact('txn'));

        $fileRef = $txn->payment_ref_id ?? $txn->connectpe_id ?? $txn->request_id ?? $txn->id;

        return $pdf->download('Invoice_'.$fileRef.'.pdf');

    }
}
