<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComplainReportController extends Controller
{
    public function complainReport()
    {
        return view('ComplainReport.index');
    }
}
