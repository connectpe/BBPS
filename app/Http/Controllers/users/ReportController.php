<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function RechargeReport()
    {
        return view('Users.reports.recharge_report');
    }

    public function BankingTransactionReport()
    {
        return view('Users.reports.banking_report');
    }

    public function UtilityTransactionReport()
    {
        return view('Users.reports.utility_bills');
    }
}
