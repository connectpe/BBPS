<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function transactionStatus()
    {
        return view('Transaction.transaction-status');
    }


    public function transactionComplaint()
    {
        return view('Transaction.transaction-complaint');
    }


    public function complaintStatus()
    {
        return view('Transaction.complaint-status');
    }

    public function transaction_Report(){
        return view('Transaction.transaction-report');
    }
}

