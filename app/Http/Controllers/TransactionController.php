<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintsCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function transactionStatus()
    {
        return view('Transaction.transaction-status');
    }

    public function transactionStatusCheck(Request $request)
    {
        $request->validate([
            'txn_id' => 'nullable|string',
            'mobile' => 'nullable|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {

            $query = Transaction::query();

            // If txn_id provided → exact match
            if ($request->txn_id) {
                $query->where('payment_ref_id', $request->txn_id);
            }

            // If mobile provided
            if ($request->mobile) {
                $query->where('mobile_number', $request->mobile);
            }

            // Date range filter
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('created_at', [
                    $request->from_date.' 00:00:00',
                    $request->to_date.' 23:59:59',
                ]);
            }

            $transactions = $query->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No record found',
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transactions->map(function ($txn) {
                    return [
                        'amount' => $txn->amount,
                        'status' => $txn->status,
                        'reference_number' => $txn->reference_number,
                        'request_id' => $txn->request_id,
                        'mobile_number' => $txn->mobile_number,
                        'payment_ref_id' => $txn->payment_ref_id,
                        'connectpe_id' => $txn->connectpe_id,
                        'created_at' => $txn->created_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error'.$e->getMessage(),
            ]);
        }
    }

    public function transactionComplaint()
    {

        DB::beginTransaction();
        try {

            $priorities = ['Low', 'Medium', 'High'];

            $services = UserService::with('service:id,service_name')->select('service_id')->where('user_id', Auth::user()->id)->where('status', 'approved')->where('is_active', '1')->orderBy('id', 'desc')->get();
            $categories = ComplaintsCategory::select('id', 'category_name')->where('status', '1')->orderBy('id', 'desc')->get();
            DB::commit();

            return view('Transaction.transaction-complaint', compact('services', 'priorities', 'categories'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error'.$e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'reference_id' => 'nullable|string|required_without:mobile|exists:transactions,payment_ref_id|max:255',
            'mobile' => 'nullable|regex:/^[0-9]{10}$/|required_without:reference_id|exists:transactions,mobile_number',
            'txn_date' => 'required|date|before_or_equal:today',
            'service_id' => 'required|exists:global_services,id',
            'priority' => 'required|in:Low,High,Medium',
            'category' => 'required|exists:complaints_categories,id',
            'description' => 'required|string|min:20|max:500',
            'attachment' => 'nullable|file|max:2048|mimes:jpg,png,jpeg',
        ], [
            'reference_id.required_without' => 'Reference ID is required when mobile number is not provided.',
            'reference_id.exists' => 'The provided reference ID does not exist in transactions.',
            'reference_id.max' => 'Reference ID may not be greater than 255 characters.',

            'mobile.required_without' => 'Mobile number is required when reference ID is not provided.',
            'mobile.regex' => 'Mobile number must be exactly 10 digits.',
            'mobile.exists' => 'The provided mobile number does not exist in transactions.',

            'txn_date.required' => 'Transaction date is required.',
            'txn_date.date' => 'Transaction date must be a valid date.',
            'txn_date.before_or_equal' => 'Transaction date cannot be in the future.',

            'service_id.required' => 'Service selection is required.',
            'service_id.exists' => 'Selected service does not exist.',

            'priority.required' => 'Priority is required.',
            'priority.in' => 'Priority must be one of Low, Medium, or High.',

            'category.required' => 'Category is required.',
            'category.exists' => 'Selected category does not exist.',

            'description.required' => 'Description is required.',
            'description.string' => 'Description must be text.',
            'description.min' => 'Description must be at least 20 characters.',
            'description.max' => 'Description may not be greater than 500 characters.',

            'attachment.file' => 'Attachment must be a valid file.',
            'attachment.max' => 'Attachment size may not be greater than 2MB.',
            'attachment.mimes' => 'Attachment must be a file of type: jpg, png, jpeg.',
        ]);

        DB::beginTransaction();

        try {

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
                'payment_ref_id' => $request->reference_id,
                'mobile_number' => $request->mobile,
                'transaction_date' => $request->txn_date,
                'priority' => $request->priority,
                'attachment_file' => $attachmentPath,
                'description' => $request->description,
                'updated_by' => $userId,
            ];

            $complaint = Complaint::create($data);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint Registered Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : '.$e->getMessage(),
            ]);
        }
    }

    public function complaintStatus()
    {

        DB::beginTransaction();
        try {

            $categories = ['transaction', 'refund', 'service', 'other'];
            DB::commit();

            return view('Transaction.complaint-status', compact('categories'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error'.$e->getMessage(),
            ]);
        }
    }

    public function checkComplaintStatus(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            $complaint = Complaint::where('ticket_number', $request->ticket_number)
                ->where('user_id', auth()->id()) // user sirf apni complaint check kare
                ->first();

            if (! $complaint) {
                return response()->json([
                    'status' => false,
                    'message' => 'Complaint not found for given Ticket Number.',
                ], 404);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'data' => [
                    'ticket_number' => $complaint->ticket_number,
                    'service_name' => $complaint->service?->service_name,
                    'resolved_at' => $complaint->resolved_at ? $complaint->resolved_at->format('M-d-Y h:i:s a') : '-',
                    'complaint_status' => $complaint->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error'.$e->getMessage(),
            ]);
        }
    }

    public function transaction_Report()
    {
        return view('Transaction.transaction-report');
    }

    public function downloadInvoice($id)
    {
        DB::beginTransaction();
        try {

            $txn = Transaction::with(['user', 'user.business'])
                ->where('id', $id)
                ->where('status', 'processed')
                ->firstOrFail();

            $pdf = Pdf::loadView('Users.reports.recharge-transaction-invoice', compact('txn'));
            $fileRef = $txn->payment_ref_id ?? $txn->connectpe_id ?? $txn->request_id ?? $txn->id;
            DB::commit();

            return $pdf->download('Invoice_'.$fileRef.'.pdf');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error'.$e->getMessage(),
            ]);
        }
    }

    public function payouttransaction()
    {
        $users = User::whereHas('orders')->select('id', 'name', 'email')->orderBy('name')->get();
        return view('Transaction.payout-transaction', compact('users'));
    }
}
