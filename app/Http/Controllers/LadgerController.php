<?php

namespace App\Http\Controllers;

class LadgerController extends Controller
{
    public function index()
    {
        return view('ladger.index');
    }


    public function reports()
    {
        return view('Reports.reports');
    }
}
