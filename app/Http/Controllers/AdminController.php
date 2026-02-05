<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\BusinessCategory;
use App\Models\BusinessInfo;
use App\Models\GlobalService;
use App\Models\OauthUser;
use App\Models\Provider;
use App\Models\Scheme;
use App\Models\SchemeRule;
use App\Models\User;
use App\Models\UserAssignedToSupport;
use App\Models\UserConfig;
use App\Models\UsersBank;
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

            if (in_array($role, [2, 3])) {
                $data['saltKeys'] = OauthUser::where('user_id', auth()->id())
                    ->where('is_active', '1')
                    ->select('client_id', 'client_secret', 'created_at')
                    ->get();
            }

            $data['activeService'] = GlobalService::where(['is_active' => '1'])
                ->select('id', 'slug', 'service_name')
                ->get();

            $data['userdata'] = User::where('id', $userId)->select('name', 'email', 'mobile', 'status', 'role_id', 'profile_image')->first();
            $data['businessInfo'] = BusinessInfo::where('user_id', $userId)->first();
            $data['businessCategory'] = BusinessCategory::where('status', 1)->orderBy('id', 'desc')->get();

            $data['usersBank'] = UsersBank::where('user_id', $userId)->first();

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

    public function dashboard()
    {
        $role = Auth::user()->role_id;

        if (in_array($role, [1, 2, 4])) {
            return view('Dashboard/dashboard');
        } elseif (in_array($role, [3])) {
            return view('Dashboard/api-dashboard');
        }
    }

    public function disableUserService(Request $request)
    {

        try {

            if (! auth()->check() && auth::user()->role_id != '1') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|string|max:50',
                'type' => 'required|string|in:is_api_allowed,is_active',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = GlobalService::find($request->service_id);

            if (! $data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found.',
                ]);
            }

            switch ($request->type) {
                case 'is_api_allowed':
                    $data->is_activation_allowed = $data->is_activation_allowed == '1' ? '0' : '1';
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'API Activation Updated  Successfully',
                    ]);
                    break;
                case 'is_active':
                    $data->is_active = $data->is_active == '1' ? '0' : '1';
                    $data->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Service Status Updated  Successfully',
                    ]);
                    break;
                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid type provided',
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addService(Request $request)
    {
        try {

            if (! auth()->check() || auth()->user()->role_id != '1') {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $request->validate([
                'service_name' => 'required|string|max:50|unique:global_services,service_name',
            ]);

            $data = [
                'service_name' => $request->service_name,
                'slug' => Str::slug($request->service_name),
            ];

            $service = GlobalService::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Service added successfully',
                'data' => $service,
            ], 201);
        } catch (\Exception $e) {
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
                'service_name' => 'required|string|max:50|unique:global_services,service_name,'.$serviceId,
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

            return response()->json([
                'status' => true,
                'message' => 'User status updated  successfully',

            ], 200);
        } catch (Exception $e) {
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
        $globalServices = GlobalService::where('is_active', '1')->select('id', 'service_name')->orderBy('id', 'desc')->get();

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
                'message' => 'Error : '.$e->getMessage(),
            ], 500);
        }
    }

    public function editProvider(Request $request, $Id)
    {

        $request->validate(
            [
                'serviceId' => 'required|exists:global_services,id',
                'providerName' => 'required|string|max:100|unique:providers,provider_name,'.$Id,
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
                'message' => 'Error : '.$e->getMessage(),
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
                    'message' => 'Error : '.$e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : '.$e->getMessage(),
            ]);
        }
    }

    public function editScheme($id)
    {

        $scheme = Scheme::with('rules')->find($id);

        if (! $scheme) {
            return response()->json(['status' => false, 'message' => 'Scheme not found'], 404);
        }

        return response()->json([
            'status' => true,
            'scheme' => $scheme,
        ]);
    }

    public function updateSchemeAndRule(Request $request, $schemeId)
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
                    'message' => 'Error : '.$e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : '.$e->getMessage(),
            ]);
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
                'message' => 'Error : '.$e->getMessage(),
            ]);
        }
    }

    public function editAssignedScheme($id)
    {
        $config = UserConfig::find($id);
        if (! $config) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $config]);
    }

    public function updateAssignedSchemetoUser(Request $request, $configId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:user_configs,user_id,'.$configId,
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

            return response()->json(['status' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function deleteAssignedScheme($id)
    {
        try {
            $config = UserConfig::find($id);
            if (! $config) {
                return response()->json(['status' => false, 'message' => 'Record not found']);
            }

            $config->delete();

            return response()->json([
                'status' => true,
                'message' => 'Relation deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function UserassigntoSupport()
    {
        $data['users'] = User::whereNotIn('role_id', [1, 4])->where('status', '1')->get();
        $data['supportStaffs'] = User::where('role_id', 4)->where('status', '1')->get();
        $data['alreadyAssignedIds'] = UserAssignedToSupport::pluck('user_id')->toArray();
        $data['assignedUsers'] = User::whereIn('id', UserAssignedToSupport::query()->distinct()->pluck('user_id'))->orderBy('name')->get();
        $data['assignedSupports'] = User::whereIn('id', UserAssignedToSupport::query()->distinct()->pluck('assined_to'))->orderBy('name')->get();
        return view('AssignuserSupport.index', $data);
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
            return response()->json(['status' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function editSupportAssignment($id)
    {
        $data = UserAssignedToSupport::find($id);
        if ($data) {
            return response()->json(['status' => true, 'data' => $data]);
        }

        return response()->json(['status' => false, 'message' => 'Not Found']);
    }

    public function deleteSupportAssignment($id)
    {
        try {
            $assignment = UserAssignedToSupport::find($id);

            if (! $assignment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found or already deleted.',
                ], 404);
            }

            $assignment->delete();

            return response()->json([
                'status' => true,
                'message' => 'Assignment removed successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }
}
