<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\BusinessCategory;
use App\Models\BusinessInfo;
use App\Models\ComplaintsCategory;
use App\Models\DefaultProvider;
use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\Provider;
use App\Models\Scheme;
use App\Models\SchemeRule;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAssignedToSupport;
use App\Models\UserConfig;
use App\Models\UsersBank;
use App\Models\UserService;
use App\Models\WebHookUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function adminProfile($userId)
    {
        try {
            CommonHelper::checkAuthUser();
            $userId = auth()->id();
            $role = Auth::user()->role_id;

            if (in_array($role, [2, 3, 4])) {
                $data['saltKeys'] = OauthUser::where('user_id', auth()->id())
                    ->where('is_active', '1')
                    ->select('client_id', 'client_secret', 'created_at')
                    ->get();
            }

            // $data['activeService'] = GlobalService::where(['is_active' => '1'])

            //     ->select('id', 'slug', 'service_name')
            //     ->get();

            $data['userdata'] = User::where('id', $userId)->select('name', 'email', 'mobile', 'status', 'role_id', 'profile_image', 'transaction_amount', 'created_at')->first();
            $data['businessInfo'] = BusinessInfo::select('user_id', 'business_category_id', 'business_name', 'gst_number', 'business_pan_number', 'business_email', 'business_phone', 'business_document', 'address', 'city', 'state', 'pincode', 'business_pan_name', 'pan_number', 'pan_owner_name', 'aadhar_number', 'aadhar_name', 'bank_id', 'pancard_image', 'aadhar_front_image', 'aadhar_back_image', 'business_type', 'cin_no', 'is_kyc')
                ->where('user_id', $userId)->first();
            $data['businessCategory'] = BusinessCategory::select('id', 'name')->where('status', 1)->orderBy('id', 'desc')->get();
            $data['supportRepresentative'] = UserAssignedToSupport::where('user_id', $userId)->with('assigned_support:id,name,email,mobile')->first();

            $data['usersBank'] = UsersBank::select(
                'account_number',
                'ifsc_code',
                'branch_name',
                'bank_docs',
                'benificiary_name',
                'ifsc_code',
                'branch_name',
                'bank_docs'
            )->where('user_id', $userId)->first();
            $data['UserServices'] = UserService::with('service:id,slug,service_name')->where('user_id', $userId)->where('status', 'approved')->where('is_active', '1')->get();
            $data['webhookUrl'] = WebHookUrl::select('url')->where('user_id', $userId)->first();
            $data['txnStats'] = Transaction::where('user_id', $userId)->where('status', 'processed')
                ->selectRaw('COUNT(id) as total_count, SUM(amount) as total_amount, MIN(created_at) as first_txn_date')
                ->first();
            if (in_array($role, [1])) {
                $data['txnStats'] = Transaction::where('status', 'processed')
                    ->selectRaw('COUNT(id) as total_count, SUM(amount) as total_amount, MIN(created_at) as first_txn_date')
                    ->first();
            }


            $data['walletBalance'] = $data['userdata']->transaction_amount ?? 0;
            $data['completedTxn'] = $data['txnStats']->total_count ?? 0;
            $data['totalSpent'] = $data['txnStats']->total_amount ?? 0;

            return view('Admin.profile')->with($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $data['activeService'] = GlobalService::where(['is_active' => '1'])
            ->select('id', 'slug', 'service_name')
            ->get();

        // dd($data);
        return view('Admin.profile')->with($data);
    }

    // public function dashboard()
    // {
    //     $role = Auth::user()->role_id;

    //     if (in_array($role, [1])) {
    //         return view('Dashboard.dashboard');
    //     } elseif (in_array($role, [3])) {
    //         return view('Dashboard.api-dashboard');
    //     } elseif ($role == 4) {
    //         return view('Dashboard.support-dashboard');
    //     } elseif ($role == 2) {
    //         return view('Dashboard.user-dashboard');
    //     }
    // }

    public function dashboard()
    {
        $role = Auth::user()->role_id;

        try {
            switch ($role) {
                case 1:
                    return redirect()->route('admin.dashboard');
                case 2:
                    return redirect()->route('user.dashboard');
                case 3:
                    return redirect()->route('api.dashboard');
                case 4:
                    return redirect()->route('support.dashboard');
                default:
                    return redirect()->route('unauthrized.page');
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }


    public function disableUserService(Request $request)
    {
        // Authorization check
        if (!auth()->check() || auth()->user()->role_id != '1') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|string|max:50',
            'type'       => 'required|string|in:is_api_allowed,is_active',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Wrap DB operations in a transaction
        try {
            DB::transaction(function () use ($request) {

                $data = GlobalService::find($request->service_id);

                if (! $data) {
                    throw new \Exception('Service not found.');
                }

                switch ($request->type) {
                    case 'is_api_allowed':
                        $data->is_activation_allowed = $data->is_activation_allowed == '1' ? '0' : '1';
                        $data->save();
                        break;

                    case 'is_active':
                        $data->is_active = $data->is_active == '1' ? '0' : '1';
                        $data->save();
                        break;

                    default:
                        throw new \Exception('Invalid type provided');
                }
            });

            // If we reach here, transaction was successful
            $message = $request->type === 'is_api_allowed'
                ? 'API Activation Updated Successfully'
                : 'Service Status Updated Successfully';

            return response()->json([
                'status'  => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }


    public function addService(Request $request)
    {

        if (! auth()->check() || auth()->user()->role_id != '1') {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'service_name' => 'required|string|max:50|unique:global_services,service_name',
        ]);

        DB::beginTransaction();
        try {

            $data = [
                'service_name' => $request->service_name,
                'slug' => Str::slug($request->service_name),
            ];

            $service = GlobalService::create($data);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service added successfully',
                'data' => $service,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editService(Request $request, $serviceId)
    {

        DB::beginTransaction();
        try {

            if (! auth()->check() || auth()->user()->role_id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $request->validate([
                'service_name' => 'required|string|max:50|unique:global_services,service_name,' . $serviceId,
            ]);

            $service = GlobalService::where('id', $serviceId)->first();

            if (! $service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }

            $service->service_name = $request->service_name;
            $service->slug = Str::slug($request->service_name);
            $service->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Service name updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateUser()
    {
        if (! auth()->check() && auth::user()->role_id == '1') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function UserStatusChange(Request $request, $userId)
    {

        DB::beginTransaction();
        try {

            $this->validateUser();

            $request->validate([
                'status' => 'required|in:0,1,2,3,4',
            ]);

            $userId = decrypt($userId);
            $user = User::where('id', $userId)->first();

            if (! $user) {

                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ]);
            }

            $user->status = $request->status;
            $user->updated_at = now();
            $user->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User status updated  successfully',

            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function changeUserStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'status' => 'required|in:0,1,2,3,4',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->id);

            $user->status = $request->status;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status. Please try again.',
            ], 500);
        }
    }

    public function providers()
    {
        try {
            $globalServices = GlobalService::select('id', 'service_name')->where('is_active', '1')->select('id', 'service_name')->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage()
            ]);
        }
        return view('Provider.providers', compact('globalServices'));
    }

    public function addProvider(Request $request)
    {
        $request->validate(
            [
                'serviceId' => 'required|exists:global_services,id',
                'providerName' => 'required|string|max:100|unique:providers,provider_name',
            ],
            [
                'serviceId.required' => 'Please select a service.',
                'serviceId.exists' => 'The selected service is invalid.',

                'providerName.required' => 'Provider name is required.',
                'providerName.string' => 'Provider name must be a valid text.',
                'providerName.max' => 'Provider name may not be greater than 100 characters.',
                'providerName.unique' => 'Duplicate Provider Name.',
            ]
        );

        DB::beginTransaction();

        try {

            $data = [
                'service_id' => $request->serviceId,
                'provider_name' => $request->providerName,
                'provider_slug' => Str::slug($request->providerName),
                'updated_by' => Auth::user()->id,
            ];

            $provider = Provider::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Provider Added Successfully',
                'data' => $provider,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editProvider(Request $request, $Id)
    {

        $request->validate(
            [
                'serviceId' => 'required|exists:global_services,id',
                'providerName' => 'required|string|max:100|unique:providers,provider_name,' . $Id,
            ],
            [
                'serviceId.required' => 'Please select a service.',
                'serviceId.exists' => 'The selected service is invalid.',
                'providerName.required' => 'Provider name is required.',
                'providerName.string' => 'Provider name must be a valid text.',
                'providerName.max' => 'Provider name may not be greater than 100 characters.',
                'providerName.unique' => 'Duplicate Provider Name.',
            ]
        );

        DB::beginTransaction();

        try {

            $data = [
                'service_id' => $request->serviceId,
                'provider_name' => $request->providerName,
                'provider_slug' => Str::slug($request->providerName),
                'updated_by' => Auth::user()->id,
            ];

            $provider = Provider::find($Id)->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Provider Updated Successfully',
                'data' => $provider,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function statusProvider(Request $request, $Id)
    {
        DB::beginTransaction();
        try {
            $provider = Provider::find($Id);
            if (! $provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Provider not Found',
                ]);
            }

            $provider->is_active = $provider->is_active == '1' ? '0' : '1';
            $provider->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Provider Status Changed Sucessfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addSchemeAndRule(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'scheme_name' => 'required|string|max:255|unique:schemes,scheme_name',
                'rules' => 'required|array|min:1',
                'rules.*.service_id' => 'required|integer|exists:global_services,id',
                'rules.*.start_value' => 'required|numeric|min:0',
                'rules.*.end_value' => 'required|numeric|gte:rules.*.start_value',
                'rules.*.type' => 'required|in:Percentage,Fixed',
                'rules.*.fee' => 'required|numeric|min:0',
                'rules.*.min_fee' => 'required|numeric|min:0',
                'rules.*.max_fee' => 'required|numeric|gte:rules.*.min_fee',
            ], [

                'scheme_name.required' => 'Scheme name is required.',
                'scheme_name.string' => 'Scheme name must be a string.',
                'scheme_name.max' => 'Scheme name may not be greater than 255 characters.',

                // Rules array
                'rules.required' => 'At least one rule is required.',
                'rules.array' => 'Rules must be an array.',
                'rules.min' => 'At least one rule must be provided.',

                // Rules.* fields
                'rules.*.service_id.required' => 'Service ID is required.',
                'rules.*.service_id.integer' => 'Service ID must be a number.',
                'rules.*.service_id.exists' => 'Selected service does not exist.',

                'rules.*.start_value.required' => 'Start value is required.',
                'rules.*.start_value.numeric' => 'Start value must be a number.',
                'rules.*.start_value.min' => 'Start value must be at least 0.',

                'rules.*.end_value.required' => 'End value is required.',
                'rules.*.end_value.numeric' => 'End value must be a number.',
                'rules.*.end_value.gte' => 'End value must be greater than or equal to start value.',

                'rules.*.type.required' => 'Type is required.',
                'rules.*.type.in' => 'Type must be either Percentage or Fixed.',

                'rules.*.fee.required' => 'Fee is required.',
                'rules.*.fee.numeric' => 'Fee must be a number.',
                'rules.*.fee.min' => 'Fee must be at least 0.',

                'rules.*.min_fee.required' => 'Minimum fee is required.',
                'rules.*.min_fee.numeric' => 'Minimum fee must be a number.',
                'rules.*.min_fee.min' => 'Minimum fee must be at least 0.',

                'rules.*.max_fee.required' => 'Maximum fee is required.',
                'rules.*.max_fee.numeric' => 'Maximum fee must be a number.',
                'rules.*.max_fee.gte' => 'Maximum fee must be greater than or equal to minimum fee.',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            try {

                $updatedBy = Auth::user()->id;

                $scheme = Scheme::create([
                    'scheme_name' => $request->scheme_name,
                    'updated_by' => $updatedBy,
                ]);

                foreach ($request->rules as $rule) {
                    SchemeRule::create([
                        'scheme_id' => $scheme->id,
                        'service_id' => $rule['service_id'],
                        'start_value' => $rule['start_value'],
                        'end_value' => $rule['end_value'],
                        'type' => $rule['type'],
                        'fee' => $rule['fee'],
                        'min_fee' => $rule['min_fee'],
                        'max_fee' => $rule['max_fee'],
                        'updated_by' => $updatedBy,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Scheme and Rules are Created Successfully',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Error : ' . $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function editScheme($id)
    {
        try {

            $scheme = Scheme::with('rules')->find($id);

            if (! $scheme) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'scheme' => $scheme,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSchemeAndRule(Request $request, $schemeId)
    {
        // Validation
        $validator = Validator::make($request->all(), [

            'scheme_name' => 'required|string|max:255|unique:schemes,scheme_name,' . $schemeId,
            'rules' => 'required|array|min:1',
            'rules.*.rule_id' => 'nullable|integer|exists:scheme_rules,id',
            'rules.*.service_id' => 'required|integer|exists:global_services,id',
            'rules.*.start_value' => 'required|numeric|min:0',
            'rules.*.end_value' => 'required|numeric|gte:rules.*.start_value',
            'rules.*.type' => 'required|in:Percentage,Fixed',
            'rules.*.fee' => 'required|numeric|min:0',
            'rules.*.min_fee' => 'required|numeric|min:0',
            'rules.*.max_fee' => 'required|numeric|gte:rules.*.min_fee',
            'rules.*.is_active' => 'nullable|boolean',
        ], [
            'scheme_name.required' => 'Scheme name is required.',
            'scheme_name.string' => 'Scheme name must be a string.',
            'scheme_name.max' => 'Scheme name may not be greater than 255 characters.',

            'rules.required' => 'At least one rule is required.',
            'rules.array' => 'Rules must be an array.',
            'rules.min' => 'At least one rule must be provided.',

            'rules.*.service_id.required' => 'Service ID is required.',
            'rules.*.service_id.integer' => 'Service ID must be a number.',
            'rules.*.service_id.exists' => 'Selected service does not exist.',

            'rules.*.start_value.required' => 'Start value is required.',
            'rules.*.start_value.numeric' => 'Start value must be a number.',
            'rules.*.start_value.min' => 'Start value must be at least 0.',

            'rules.*.end_value.required' => 'End value is required.',
            'rules.*.end_value.numeric' => 'End value must be a number.',
            'rules.*.end_value.gte' => 'End value must be greater than or equal to start value.',

            'rules.*.type.required' => 'Type is required.',
            'rules.*.type.in' => 'Type must be either Percentage or Fixed.',

            'rules.*.fee.required' => 'Fee is required.',
            'rules.*.fee.numeric' => 'Fee must be a number.',
            'rules.*.fee.min' => 'Fee must be at least 0.',

            'rules.*.min_fee.required' => 'Minimum fee is required.',
            'rules.*.min_fee.numeric' => 'Minimum fee must be a number.',
            'rules.*.min_fee.min' => 'Minimum fee must be at least 0.',

            'rules.*.max_fee.required' => 'Maximum fee is required.',
            'rules.*.max_fee.numeric' => 'Maximum fee must be a number.',
            'rules.*.max_fee.gte' => 'Maximum fee must be greater than or equal to minimum fee.',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',

                'errors' => $validator->errors(),

            ], 422);
        }

        DB::beginTransaction();

        try {

            $updatedBy = Auth::user()->id;
            $scheme = Scheme::findOrFail($schemeId);

            if (! $scheme) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not found',
                ]);
            }

            $scheme->update([
                'scheme_name' => $request->scheme_name,

                'updated_by' => $updatedBy,
            ]);

            $existingRuleIds = [];

            foreach ($request->rules as $ruleData) {

                if (! empty($ruleData['rule_id'])) {
                    // Update existing rule
                    $rule = SchemeRule::findOrFail($ruleData['rule_id']);
                    $rule->update([
                        'service_id' => $ruleData['service_id'],
                        'start_value' => (float) $ruleData['start_value'],
                        'end_value' => (float) $ruleData['end_value'],
                        'type' => $ruleData['type'],
                        'fee' => (float) $ruleData['fee'],
                        'min_fee' => (float) $ruleData['min_fee'],
                        'max_fee' => (float) $ruleData['max_fee'],
                        'updated_by' => $updatedBy,
                    ]);

                    $existingRuleIds[] = $rule->id;
                } else {
                    // Insert new rule
                    $newRule = SchemeRule::create([
                        'scheme_id' => $scheme->id,
                        'service_id' => $ruleData['service_id'],
                        'start_value' => (float) $ruleData['start_value'],
                        'end_value' => (float) $ruleData['end_value'],
                        'type' => $ruleData['type'],
                        'fee' => (float) $ruleData['fee'],
                        'min_fee' => (float) $ruleData['min_fee'],
                        'max_fee' => (float) $ruleData['max_fee'],
                        'updated_by' => $updatedBy,

                    ]);

                    $existingRuleIds[] = $newRule->id;
                }
            }

            // Optional: delete rules removed from the request
            SchemeRule::where('scheme_id', $scheme->id)
                ->whereNotIn('id', $existingRuleIds)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Scheme and Rules Updated Successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignSchemetoUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:user_configs,user_id',
            'scheme_id' => 'required|exists:schemes,id',
        ], [
            'user_id.required' => 'User Id is required',
            'user_id.exists' => 'User Id doesn\'t exists',
            'user_id.unique' => 'Scheme already assigned to this user',

            'scheme_id.required' => 'Scheme Id is required',
            'scheme_id.exists' => 'Scheme Id doesn\'t exists',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $updatedBy = Auth::user()->id;

            $data = [
                'user_id' => $request->user_id,
                'scheme_id' => $request->scheme_id,
                'updated_by' => $updatedBy,
            ];

            $insert = UserConfig::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Scheme assigned Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function editAssignedScheme($id)
    {
        try {

            $config = UserConfig::find($id);

            if (! $config) {
                return response()->json([
                    'status' => false,
                    'message' => 'Not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $config,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAssignedSchemetoUser(Request $request, $configId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:user_configs,user_id,' . $configId,
            'scheme_id' => 'required|exists:schemes,id',
        ], [
            'user_id.required' => 'User Id is required',
            'user_id.exists' => 'User Id doesn\'t exists',
            'user_id.unique' => 'Scheme already assigned to this user',
            'scheme_id.required' => 'Scheme Id is required',
            'scheme_id.exists' => 'Scheme Id doesn\'t exists',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $config = UserConfig::find($configId);
            if (! $config) {
                return response()->json(['status' => false, 'message' => 'User Configuration not found']);
            }

            $updatedBy = Auth::user()->id;
            $config->update([
                'user_id' => $request->user_id,
                'scheme_id' => $request->scheme_id,
                'updated_by' => $updatedBy,
            ]);

            if ($request->has('rules') && is_array($request->rules)) {
                $existingRuleIds = [];
                foreach ($request->rules as $ruleData) {
                    if (! empty($ruleData['rule_id'])) {
                        $rule = SchemeRule::findOrFail($ruleData['rule_id']);
                        $rule->update([
                            'service_id' => $ruleData['service_id'],
                            'start_value' => (float) $ruleData['start_value'],
                            'end_value' => (float) $ruleData['end_value'],
                            'type' => $ruleData['type'],
                            'fee' => (float) $ruleData['fee'],
                            'min_fee' => (float) $ruleData['min_fee'],
                            'max_fee' => (float) $ruleData['max_fee'],
                            'updated_by' => $updatedBy,
                        ]);
                        $existingRuleIds[] = $rule->id;
                    } else {
                        $newRule = SchemeRule::create([
                            'scheme_id' => $request->scheme_id,
                            'service_id' => $ruleData['service_id'],
                            'start_value' => (float) $ruleData['start_value'],
                            'end_value' => (float) $ruleData['end_value'],
                            'type' => $ruleData['type'],
                            'fee' => (float) $ruleData['fee'],
                            'min_fee' => (float) $ruleData['min_fee'],
                            'max_fee' => (float) $ruleData['max_fee'],
                            'updated_by' => $updatedBy,
                        ]);
                        $existingRuleIds[] = $newRule->id;
                    }
                }
                SchemeRule::where('scheme_id', $request->scheme_id)
                    ->whereNotIn('id', $existingRuleIds)
                    ->delete();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'User Scheme Relation Updated Successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAssignedScheme($id)
    {
        DB::beginTransaction();
        try {
            $config = UserConfig::find($id);
            if (! $config) {
                return response()->json(['status' => false, 'message' => 'Record not found']);
            }

            $config->delete();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Relation deleted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error : ' . $e->getMessage()]);
        }
    }

    public function UserassigntoSupport()
    {


        DB::beginTransaction();
        try {
            $data['users'] = User::select('id', 'name')->whereNotIn('role_id', [1, 4])->where('status', '1')->get();
            $data['supportStaffs'] = User::select('id', 'name')->where('role_id', 4)->where('status', '1')->get();
            $data['alreadyAssignedIds'] = UserAssignedToSupport::pluck('user_id')->toArray();
            $data['assignedUsers'] = User::whereIn('id', UserAssignedToSupport::query()->distinct()->pluck('user_id'))->orderBy('name')->get();
            $data['assignedSupports'] = User::whereIn('id', UserAssignedToSupport::query()->distinct()->pluck('assined_to'))->orderBy('name')->get();

            DB::commit();
            return view('AssignuserSupport.index', $data);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function UserAssignedtoSupportuser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|array',
            'user_id.*' => 'exists:users,id',
            'assined_to' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        try {
            DB::beginTransaction();
            $assignedTo = $request->assined_to;
            $updatedBy = auth()->id();
            $userIds = $request->user_id;
            $isEdit = $request->filled('assignment_id');
            if ($isEdit) {
                UserAssignedToSupport::where('id', $request->assignment_id)->delete();
                $msg = 'Assignment updated successfully!';
            } else {
                $msg = 'Users assigned successfully!';
            }
            foreach ($userIds as $userId) {
                UserAssignedToSupport::create([
                    'user_id' => $userId,
                    'assined_to' => $assignedTo,
                    'updated_by' => $updatedBy,
                ]);
            }
            DB::commit();

            return response()->json(['status' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function editSupportAssignment($id)
    {
        try {
            $data = UserAssignedToSupport::find($id);
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Not Found']);
            }
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteSupportAssignment($id)
    {
        DB::beginTransaction();
        try {
            $assignment = UserAssignedToSupport::find($id);

            if (! $assignment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found or already deleted.',
                ], 404);
            }

            $assignment->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Assignment removed successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function supportdetails()
    {
        return view('AssignuserSupport.support-user-details');
    }

    public function addSupportMember(Request $request)
    {
        DB::beginTransaction();
        try {
            if (! Auth::user()->role_id == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'unauthorized access',
                ]);
            }
            $request->validate([
                'name' => 'required|string|min:3',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|digits:10',
                'password' => 'required|min:6',
                'password' => 'required|min:6|confirmed',
            ]);

            $payload = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => bcrypt($request->password),
                'email_verfied_at' => now(),
                'role_id' => '4',
                'status' => '1',
            ];

            $member = User::create($payload);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Member created Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getSupportMember($id)
    {

        try {
            $user = User::find($id);
            return $user ? response()->json(['status' => true, 'data' => $user]) : response()->json(['status' => false]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function editSupportMember(Request $request, $user_id)
    {
        DB::beginTransaction();
        try {
            if (Auth::user()->role_id != 1) {
                return response()->json(['status' => false, 'message' => 'unauthorized access']);
            }

            $member = User::find($user_id);
            if (! $member) {
                return response()->json(['status' => false, 'message' => 'User not found']);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $user_id,
                'mobile' => 'required|digits:10|unique:users,mobile,' . $user_id,

            ]);

            $member->name = $request->name;
            $member->email = $request->email;
            $member->mobile = $request->mobile;
            $member->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Member updated Successfully',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function category()
    {
        return view('Categories.index');
    }

    public function addComplaintCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:complaints_categories,category_name|regex:/^[A-Za-z0-9 _-]+$/',
        ], [
            'category_name.required' => 'Category name is required.',
            'category_name.string' => 'Category name must be a valid string.',
            'category_name.max' => 'Category name cannot exceed 50 characters.',
            'category_name.regex' => 'Category name can only contain letters, numbers, spaces, hyphens, and underscores.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $updatedBy = Auth::user()->id;

            $data = [
                'category_name' => $request->category_name,
                'updated_by' => $updatedBy,
            ];

            ComplaintsCategory::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Category Added Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function updateComplaintCategory(Request $request, $Id)
    {

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|regex:/^[A-Za-z0-9 _-]+$/|unique:complaints_categories,category_name,' . $Id,
        ], [
            'category_name.required' => 'Category name is required.',
            'category_name.string' => 'Category name must be a valid string.',
            'category_name.max' => 'Category name cannot exceed 50 characters.',
            'category_name.regex' => 'Category name can only contain letters, numbers, spaces, hyphens, and underscores.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $updatedBy = Auth::user()->id;
            $category = ComplaintsCategory::find($Id);
            if (! $category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ip address not Found',
                ]);
            }

            $data = [
                'category_name' => $request->category_name,
                'updated_by' => $updatedBy,
            ];

            $update = $category->update($data);

            DB::commit();

            return response()->json([
                'status' => true,

                'message' => 'Category Updated Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function statusComplaintCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $category = ComplaintsCategory::findOrFail($id);

            $category->update([
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Category Status Updated Successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeKycStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:business_infos,id',
            'userId' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $business = BusinessInfo::where('id', $request->id)->where('user_id', $request->userId)->first();

            if (! $business) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business Not Found',
                ], 404);
            }
            $data = [
                'is_kyc' => $business->is_kyc == '0' ? '1' : '0',
            ];

            $business->update($data);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Kyc Updated Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addDefaultProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:global_services,id|unique:default_providers,service_id',
            'provider_id' => 'required|exists:providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $updatedBy = Auth::user()->id;
            $provider = Provider::find($request->provider_id);
            $duplicateProvider = DefaultProvider::where('service_id', $request->service_id)->where('provider_id', $request->provider_id)->first();

            if (! $provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Provider not found',
                ]);
            }

            if ($duplicateProvider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Default Provider already exist for selected Service',
                ]);
            }

            $data = [
                'service_id' => $request->service_id,
                'provider_id' => $request->provider_id,
                'provider_slug' => 'default_' . $provider->provider_slug,
                'updated_by' => $updatedBy,
            ];

            $provider = DefaultProvider::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Default Provider Added Successfully',
                'data' => $provider,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editDefaultProvider(Request $request, $Id)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:global_services,id|unique:default_providers,service_id,' . $Id,
            'provider_id' => 'required|exists:providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $updatedBy = Auth::user()->id;
            $provider = Provider::find($request->provider_id);
            $defaultProvider = DefaultProvider::find($Id);
            $duplicateProvider = DefaultProvider::where('service_id', $request->service_id)
                ->where('provider_id', $request->provider_id)
                ->where('id', '!=', $Id)
                ->first();

            if (! $provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Provider not found',
                ]);
            }

            if (! $defaultProvider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Default Provider not found',
                ]);
            }

            if ($duplicateProvider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Default Provider already exist for selected Service',
                ]);
            }

            $data = [
                'service_id' => $request->service_id,
                'provider_id' => $request->provider_id,
                'provider_slug' => 'default_' . $provider->provider_slug,
                'updated_by' => $updatedBy,
            ];

            $defaultProvider->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Default Provider Updated Successfully',
                'data' => $provider,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),

            ], 500);
        }
    }

    public function defalutSlug()
    {
        try {
            $services = GlobalService::where('is_active', '1')->select('id', 'service_name')->orderBy('service_name')->get();
            return view('Provider.defaultslug', compact('services'));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function getProvidersByService($serviceId)
    {
        try {
            $providers = Provider::where('service_id', (int) $serviceId)
                ->where('is_active', '1')
                ->select('id', 'provider_name')
                ->orderBy('provider_name')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $providers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    // public function editDefaultProvider(Request $request, $Id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'service_id' => 'required|exists:global_services,id',
    //         'provider_id' => 'required|exists:providers,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $updatedBy = Auth::user()->id;
    //         $provider = Provider::find($request->provider_id);
    //         $defaultProvider = DefaultProvider::find($Id);
    //         $duplicateProvider = DefaultProvider::where('service_id', $request->service_id)
    //             ->where('provider_id', $request->provider_id)
    //             ->where('id', '!=', $Id)
    //             ->first();

    //         if (!$provider) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Provider not found',
    //             ]);
    //         }

    //         if (!$defaultProvider) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Default Provider not found',
    //             ]);
    //         }

    //         if ($duplicateProvider) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Default Provider already exist for selected Service',
    //             ]);
    //         }

    //         $data = [
    //             'service_id' => $request->service_id,
    //             'provider_id' => $request->provider_id,
    //             'provider_slug' => 'default_' . $provider->provider_slug,
    //             'updated_by' => $updatedBy,
    //         ];

    //         $defaultProvider->update($data);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Default Provider Updated Successfully',
    //             'data' => $provider,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Error : ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function nsdlPayment()
    {

        DB::beginTransaction();
        try {
            $users = User::select('id', 'name', 'email')->where('role_id', '!=', '1')->whereHas('nsdlPayments')->where('status', '!=', '0')->orderBy('id', 'desc')->get();
            $globalServices = GlobalService::select('id', 'service_name')->where('is_active', '1')->orderBy('id', 'desc')->get();
            DB::commit();
            return view('Transaction.nsdl-payment', compact('users', 'globalServices'));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }

    public function supportBasedUserList($Id)
    {

        DB::beginTransaction();
        try {
            $support = User::find($Id);
            if (! $support) {
                return response()->json([
                    'status' => false,
                    'message' => 'Support User not found',
                ]);
            }
            DB::commit();
            return view('AssignuserSupport.support-based-user-list', compact('support'));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }
}
