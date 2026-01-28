<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
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

        return view($view, [
            'page_title' => $pageTitle,
            'site_title' => $pageTitle
        ]);
    }

}
