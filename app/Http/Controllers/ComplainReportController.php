<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplainReportController extends Controller
{
    public function complainReport()
    {
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['low', 'normal', 'high', 'urgent']; 

        return view('ComplainReport.index', compact('statuses', 'priorities'));
    }

    public function fetchComplaints(Request $request)
    {
        $draw   = (int) $request->get('draw');
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $searchValue = $request->input('search.value');

        $reference = $request->input('reference_number');
        $userName  = $request->input('user_name');
        $priority  = $request->input('priority');
        $status    = $request->input('status');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = Complaint::query()->with('user');
        $recordsTotal = (clone $query)->count();

        if ($reference !== null && $reference !== '') {
            $query->where('reference_number', 'like', "%{$reference}%");
        }

        if ($userName !== null && $userName !== '') {
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', 'like', "%{$userName}%");
            });
        }

        if ($priority !== null && $priority !== '') {
            $query->where('priority', $priority);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if (($dateFrom && $dateFrom !== '') || ($dateTo && $dateTo !== '')) {
            $from = $dateFrom ? $dateFrom . ' 00:00:00' : null;
            $to   = $dateTo ? $dateTo . ' 23:59:59' : null;

            if ($from && $to) $query->whereBetween('created_at', [$from, $to]);
            elseif ($from)    $query->where('created_at', '>=', $from);
            elseif ($to)      $query->where('created_at', '<=', $to);
        }

        if ($searchValue !== null && $searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('reference_number', 'like', "%{$searchValue}%")
                  ->orWhere('service_name', 'like', "%{$searchValue}%")
                  ->orWhere('category', 'like', "%{$searchValue}%")
                  ->orWhere('priority', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%")
                  ->orWhereHas('user', function ($qq) use ($searchValue) {
                      $qq->where('name', 'like', "%{$searchValue}%");
                  });
            });
        }

        $recordsFiltered = (clone $query)->count();
        $order = $request->get('order');
        $columns = $request->get('columns');
        $orderableMap = [
            'id' => 'id',
            'reference_number' => 'reference_number',
            'service_name' => 'service_name',
            'category' => 'category',
            'priority' => 'priority',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        if (isset($order[0])) {
            $orderColIndex = $order[0]['column'];
            $orderDir = $order[0]['dir'] ?? 'desc';
            $colName = $columns[$orderColIndex]['data'] ?? 'created_at';

            if (isset($orderableMap[$colName])) {
                $query->orderBy($orderableMap[$colName], $orderDir);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $data = $query->skip($start)->take($length)->get();
        $rows = [];
        foreach ($data as $c) {
            $rows[] = [
                'id' => $c->id,
                'reference_number' => $c->reference_number,
                'user_name' => $c->user->name ?? '-',
                'service_name' => $c->service_name ?? '-',
                'category' => $c->category ?? '-',
                'priority' => $c->priority ?? '-',
                'status' => $c->status ?? '-',
                'description' => $c->description ?? '',
                'admin_notes' => $c->admin_notes ?? '-',
                'attachment_url' => $c->attachment_path ? asset('storage/app/public/' . $c->attachment_path) : null,
                'created_at' => $c->created_at ? $c->created_at->toISOString() : null,
            ];
        }

        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rows
        ]);
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
