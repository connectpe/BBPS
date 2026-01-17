<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\UsersLog;



class AuthController extends Controller
{

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'rolename' => 'required|string|max:50',
            'business_name'=> 'required|string',
            'business_pan'=> 'required|string',
            'business_type'=> 'required|string',
            'aadhar_name'=> 'required|string',
            'aadhar_number'=> 'required|string',
            'gst_number'=> 'required|string',
            'pan_owner_name'=> 'required|string',
            'pan_number'=> 'required|string',
            'address'=> 'required|string',
            'ciy'=> 'required|string',
            'state'=> 'required|string',
            'pincode'=> 'required|integer',
 
        ]);

        $role = Role::where('slug',$request->rolename)->select('id')->first();
        // dd( $role);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            
            'role_id' => $role->id,
            'password' => bcrypt($request->password),
        ]);
        $categoryId = null;
        if(!empty($request->business_type)){
            $data = BusinessInfo::where('slug',$request->business_type)->select('id')->first();
            $categoryId = $data->id;

        }
        BusinessInfo::create([
            'user_id'=>$user->id,
            'business_category_id'=>$categoryId,
            'business_name'=>$request->business_name,
            'business_pan_number'=>$request->business_pan,
            'business_pan_name'=>$request->business_pan,
            
            'aadhar_name'=>$request->aadhar_name,
            'gst_number'=>$request->gst_number,
            'pan_owner_name'=>$request->pan_owner_name,
            'pan_number'=>$request->pan_number,
            'aadhar_number'=>$request->aadhar_number,
            'address'=>$request->address,
            'city'=>$request->city,
            'state'=>$request->state,
            'pincode'=>$request->pincode,
            'bank_id'=>$bankId,
            


        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ]);
        }

        $request->session()->regenerate();
        

        

        return redirect()->route('dashboard');
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
   
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function passwordReset(Request $request){
         try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
            ]);

            $user = auth()->user();

           

           
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

           
            $user->password = Hash::make($request->new_password);
            $user->updated_at = now();

            // dd($user->updated_at);
            $user->save();


            return redirect()->back()->with([
                'data'=>'userPassUpdated',
                'status' => true,
                'message' => 'Password updated successfully'
            ]);

            // return response()->with([
            //     'data'=>'userPassUpdated'
            //     'status' => true,
            //     'message' => 'Password updated successfully'
            // ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'.$e->getMessage(),
            ], 500);
        }
    }
}





