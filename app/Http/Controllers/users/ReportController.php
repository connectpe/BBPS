<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function index($type)
    {
        switch ($type) {

            case 'recharge':
                $view = 'Users/reports/recharge_report';
                $pageTitle = 'Recharge Report';
                break;

            case 'banking':
                $view = 'Users/reports/banking_report';
                $pageTitle = 'Banking Report';
                break;

            case 'utility':
                $view = 'Users/reports/utility_bills';
                $pageTitle = 'Utility Bill Report';
                break;

            default:
                abort(404);
        }

        $users = User::where('role_id', '!=', '1')->where('role_id', '!=', '4')->where('status', '!=', '0')->orderBy('id', 'desc')->get();

        return view($view, [
            'page_title' => $pageTitle,
            'site_title' => $pageTitle,
            'users' => $users
        ]);
    }
}
