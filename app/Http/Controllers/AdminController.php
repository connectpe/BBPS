<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalService;
use App\Helpers\CommonHelper;
use App\Models\OauthUser;
use App\Models\User;
use App\Models\BusinessInfo;
use App\Models\BusinessCategory;
use App\Models\UsersBank;


class AdminController extends Controller
{
    public function adminProfile($userId)
    {
        try{
            CommonHelper::checkAuthUser();
            $userId = auth()->id();
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
            $data['userdata'] = User::where('id',$userId)->select('name','email','mobile','status','role_id')->first();
            $data['businessInfo'] = BusinessInfo::where('user_id',$userId)->first();
            // $data['businessCategory'] = BusinessCategory::where('id',$businessInfo->business_category_id)->first();
                
            $data['usersBank'] = UsersBank::where('user_id',$userId)->select('bank_name','account_number','ifsc_code','created_at')->first();

            // dd($data);
            
            return view('Admin.profile')->with($data);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    

}
