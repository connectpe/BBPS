<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalService;
use App\Helpers\CommonHelper;
use App\Models\OauthUser;


class AdminController extends Controller
{
    public function adminProfile()
    {
        CommonHelper::checkAuthUser();
        if (auth()->user()->role_id == '2') {
            $data['saltKeys'] = OauthUser::where('user_id', auth()->id())
                ->where('is_active', '1')
                ->select('client_id', 'client_secret', 'created_at')
                ->get();

            // dd($data['saltKeys']);
        }
        $data['activeService'] = GlobalService::where(['is_active' => '1'])
            ->select('id', 'slug', 'service_name')
            ->get();
        // dd($data);
        return view('Admin.profile')->with($data);
    }

    public function dashboard()
{
    return view('dashboard');
}

}
