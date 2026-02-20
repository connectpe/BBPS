<?php

namespace App\Http\Controllers;

use App\Helpers\SendingMail;
use App\Models\EmailVerification;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = User::where('email', $request->email)->first();

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:50',
                    $user && $user->status != '0' ? Rule::unique('users', 'email') : '',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ],
                'mobile' => [
                    'required',
                    'max:10',
                    'regex:/^[6-9][0-9]{9}$/',
                    $user && $user->status != '0' ? Rule::unique('users', 'mobile') : '',
                ],
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:user,reseller',
            ], [
                'role.required' => 'Role is required',
                'role.in' => 'Please select a valid role',
            ]);

            $roleSlug = $request->role;
            $role = Role::where('slug', $roleSlug)->firstOrFail();

            $otp = rand(1000, 9999);
            $first_four = substr($request->mobile, 0, 4);
            $mpin = Hash::make($first_four);

            if ($user) {
                $user = User::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->name,
                        'mobile' => $request->mobile,
                        'password' => bcrypt($request->password),
                        'role_id' => $role->id ?? 2,
                        'mpin' => $mpin,
                        'email_verified_at' => null,
                        'status' => '0',
                    ]
                );
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'role_id' => $role->id ?? 2,
                    'password' => bcrypt($request->password),
                    'mpin' => $mpin,
                    'email_verified_at' => null,
                    'status' => '0',
                ]);
            }

            try {
                $isSend = SendingMail::sendMail([
                    'name' => $request->name,
                    'email' => $request->email,
                    'otp' => $otp,

                    'subject' => 'Email Verification for Dashboard Login '

                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            // if (!$isSend) {
            //     throw new \Exception('Mail not sent');
            // }

            EmailVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'otp' => $otp,
                    'expire_at' => Carbon::now()->addMinutes(10),
                ]
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User signup successfully, OTP sent to email',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Signup failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:4',
            ]);

            $user = User::where('email', $request->email)->first();

            // dd($user);

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $otpData = EmailVerification::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->where('expire_at', '>', Carbon::now())
                ->first();
            // dd($otpData);
            if (! $otpData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 401);
            }

            $user->email_verified_at = Carbon::now();
            $user->status = '1';
            $user->save();

            $otpData->delete();

            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully',
            ]);

        } catch (\Throwable $e) {

            \Log::error('Verify OTP Error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);

        }
    }

    public function login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if ($user->status != '1') {
                $message = $this->checkUserStatus($user->status);

                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 403);
            }

            if (empty($user->email_verified_at)) {

                $otp = rand(1000, 9999);

                try {
                    $isSend = SendingMail::sendMail([
                        'name' => $user->name,
                        'email' => $user->email,
                        'otp' => $otp,
                        'subject' => 'Email Verification',
                    ]);
                } catch (\Exception $e) {

                    Log::error('Mail sending failed: '.$e->getMessage());

                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to send OTP. Please try again.',
                    ], 500);
                }

                EmailVerification::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'otp' => $otp,
                        'expire_at' => Carbon::now()->addMinutes(10),
                    ]
                );

                return response()->json([
                    'status' => true,
                    'isOtpSend' => true,
                    'email' => $user->email,
                    'message' => 'OTP sent to your email. Please verify to login.',
                ], 200);
            }

            if (! Auth::attempt(
                $request->only('email', 'password'),
                $request->boolean('remember')
            )) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                    'errors' => [
                        'email' => ['Invalid credentials'],
                    ],
                ], 422);
            }

            $request->session()->regenerate();

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'redirect' => route('dashboard'),
            ]);

        } catch (\Throwable $e) {

            Log::error('Login Error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function isInitiatedOrSuspendedOrTerminate()
    {
        try {
            if (Auth::user()->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are not a active person',
                ]);
            } elseif (Auth::user()->status == 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your Account is suspended by the administrator',
                ]);
            } elseif (Auth::user()->status == 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your Account is temporary Terminated by the admin',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'some error occur',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');

        } catch (\Exception $e) {

            \Log::error('Logout Error: '.$e->getMessage());

            return redirect('/')->with('error', 'Something went wrong while logging out.');
        }
    }

    public function passwordReset(Request $request)
    {

        try {
            $request->validate([
                'current_password' => ['required'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            ]);

            $user = auth()->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->updated_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'.$e->getMessage(),
            ], 500);
        }
    }

    protected function checkUserStatus($status)
    {
        try {

            $message = match ($status) {
                '0' => 'Your account has been Initiated.',
                '2' => 'Your account is Inactive.',
                '3' => 'Your account is Pending approval.',
                '4' => 'Your account has been Suspended.',
                default => 'Unauthorized Access.',
            };

            return $message;

        } catch (\Throwable $e) {

            \Log::error('checkUserStatus Error: '.$e->getMessage());

            return 'Unauthorized Access.';
        }
    }
}
