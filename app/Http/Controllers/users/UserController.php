<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OauthUser;
use Illuminate\Support\Facades\DB;
use App\Models\GlobalService;



class UserController extends Controller
{

    public function bbpsUsers()

    {
        return view('Users.users');
    }


    public function ajaxBbpsUsers(Request $request)
    {
        $users = [];
        $gendersArray = ['Male', 'Female', 'Other'];

        for ($i = 1; $i <= 100; $i++) {

            $randomGenderKey = array_rand($gendersArray);
            $userGender = $gendersArray[$randomGenderKey];

            $users[] = [
                'id' => $i,
                'contact_name' => "User $i",
                'email' => "user$i@test.com",
                'mobile' => rand(9999999999, 1111111111),
                'gender' => "$userGender",
                'aadhaar' => rand(999999999999, 111111111111),
                'pan' => strtoupper(Str::random(10)),
                'status' => $i % 2 == 0 ? 'Active' : 'Inactive',
            ];
        }


        if (!empty($request->name)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['name']), strtolower($request->name)));
        }
        if (!empty($request->email)) {
            $users = array_filter($users, fn($u) => str_contains(strtolower($u['email']), strtolower($request->email)));
        }
        if (!empty($request->status)) {
            $users = array_filter($users, fn($u) => $u['status'] == $request->status);
        }


        $filteredCount = count($users);

        //  Pagination (AJAX)
        $users = array_slice($users, $request->start, $request->length);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => 100,
            'recordsFiltered' => $filteredCount,
            'data' => array_values($users),
        ]);
    }

    public function generateClientCredentials(Request $request)
    {
        $request->validate([
            'service' => 'required|string|max:50',
        ]);

        // dd($request->all());

        DB::beginTransaction();

        $service = GlobalService::where('user_id', auth()->id())
            ->where(['slug' => $request->service, 'is_active' => '1'])
            ->select('id')
            ->first();

        try {

            $userId = auth()->id();

            $clientId = 'RAFI' . strtoupper($request->service) . '_' . Str::random(16);
            $clientSecret = hash('sha256', Str::random(32) . now());


            $credential = OauthUser::create([
                'user_id'       => $userId,
                'service_id'     => $service->id,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'is_active'    => '1',
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Client credentials generated successfully',
                'data'    => [
                    'client_id'     => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                ],
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Client credential generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while generating credentials',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    

    public function viewSingleUsers()
    {
        return view('Users.view-user');
    }
}
