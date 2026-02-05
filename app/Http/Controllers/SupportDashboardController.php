<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportDashboardController extends Controller
{
    public function supportUserList()
    {
        return view('Supportdashboard.userlist');
    }

    public function userComplaints()
    {
        return view('ComplainReport.support-complaint-list');
    }
}
