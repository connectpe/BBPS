<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // public function bbpsUsers()
    // {
    //     return view('Users.users');
    // }


    // public function ajaxBbpsUsers(Request $request)
    // {
    //     $users = [];
    //     $gendersArray = ['Male', 'Female', 'Other'];

    //     for ($i = 1; $i <= 100; $i++) {

    //         $randomGenderKey = array_rand($gendersArray);
    //         $userGender = $gendersArray[$randomGenderKey];

    //         $users[] = [
    //             'id' => $i,
    //             'contact_name' => "User $i",
    //             'email' => "user$i@test.com",
    //             'mobile' => rand(9999999999, 1111111111),
    //             'gender' => "$userGender",
    //             'aadhaar' => rand(999999999999, 111111111111),
    //             'pan' => strtoupper(Str::random(10)),
    //             'status' => $i % 2 == 0 ? 'Active' : 'Inactive',
    //         ];
    //     }


    //     if (!empty($request->name)) {
    //         $users = array_filter($users, fn($u) => str_contains(strtolower($u['name']), strtolower($request->name)));
    //     }
    //     if (!empty($request->email)) {
    //         $users = array_filter($users, fn($u) => str_contains(strtolower($u['email']), strtolower($request->email)));
    //     }
    //     if (!empty($request->status)) {
    //         $users = array_filter($users, fn($u) => $u['status'] == $request->status);
    //     }


    //     $filteredCount = count($users);

    //     //  Pagination (AJAX)
    //     $users = array_slice($users, $request->start, $request->length);

    //     return response()->json([
    //         'draw' => intval($request->draw),
    //         'recordsTotal' => 100,
    //         'recordsFiltered' => $filteredCount,
    //         'data' => array_values($users),
    //     ]);
    // }
}
