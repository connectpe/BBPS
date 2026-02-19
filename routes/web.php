<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BbpsRechargeController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ComplainReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LadgerController;
use App\Http\Controllers\SchemeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\users\ReportController;
use App\Http\Controllers\users\UserController;
use App\Http\Controllers\SupportDashboardController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'loginRedirect'])->name('home');
Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('auth')->name('dashboard');




Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('signup', [AuthController::class, 'signup'])->name('admin.signup');
Route::get('kyc', function () {
    return view('Users.kyc-page');
});



Route::group(['middleware' => ['auth']], function () {
    Route::group(['prefix' => 'admin'], function () {
        // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    });

    Route::group(['middleware' => ['isAdmin'], 'prefix' => 'admin'], function () {
        Route::get('/dashboard', function () {
            return view('Dashboard.dashboard');
        })->name('admin.dashboard');
        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle.user');
        Route::post('user-status-change', [AdminController::class, 'changeUserStatus'])->name('admin.user_status.change');
        Route::post('add-service', [AdminController::class, 'addService'])->name('admin.add_service');
        Route::put('edit-service/{service_id}', [AdminController::class, 'editService'])->name('admin.edit_service');

        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle');


        // Support member routes here
        Route::get('/support-details', [AdminController::class, 'supportdetails'])->name('support_details');
        Route::post('/add-s-member', [AdminController::class, 'addSupportMember'])->name('add.support.member');
        Route::get('/get-s-member/{id}', [AdminController::class, 'getSupportMember'])->name('get.support.member');
        Route::post('/edit-s-member/{user_id}', [AdminController::class, 'editSupportMember'])->name('edit.support.member');
        Route::get('support-based-user-list/{id}',[AdminController::class,'supportBasedUserList'])->name('support_based_user_list');

        // category routes
        Route::get('/categories', [AdminController::class, 'category'])->name('categories.index');
        Route::post('add-complaint-category', [AdminController::class, 'addComplaintCategory'])->name('add_complaint_category');
        Route::post('update-complaint-category/{id}', [AdminController::class, 'updateComplaintCategory'])->name('update_complaint_category');
        Route::post('status-complaint-category/{id}', [AdminController::class, 'statusComplaintCategory'])->name('status_complaint_category');
        Route::post('change-ekyc-status', [AdminController::class, 'changeKycStatus'])->name('change_ekyc_status');

        Route::get('/default-slug', [AdminController::class, 'defalutSlug'])->name('defaultslug');
        Route::get('/fetch/providers-by-service/{serviceId}', [AdminController::class, 'getProvidersByService'])->name('providers_by_service');
        Route::post('add-default-provider', [AdminController::class, 'addDefaultProvider'])->name('add-default-provider');
        Route::post('edit-default-provider/{id}', [AdminController::class, 'editDefaultProvider'])->name('edit-default-provider');

        // NSDL Transaction Routes
        Route::get('/nsdl-payment', [AdminController::class, 'nsdlPayment'])->name('nsdl-payment');


        Route::post('/users/{id}/routing/save', [UserController::class, 'saveUserRouting'])
            ->name('admin.users.routing.save');

        // admin routes
        Route::get('request-services', [ServiceRequestController::class, 'index'])->name('request_services');
        Route::get('/users', [UserController::class, 'bbpsUsers'])->name('users');
        Route::get('/view-user/{id}', [UserController::class, 'viewSingleUsers'])->name('view_user')->withoutMiddleware('isAdmin');
        Route::get('api-log', [UserController::class, 'ApiLog'])->name('api_log');
        Route::get('our-services', [ServiceController::class, 'ourService'])->name('our_servicess');
        Route::post('active-user-service-status', [ServiceController::class, 'activeUserService'])->name('active_user_service_status');
        Route::post('/admin/service/add', [AdminController::class, 'AddService'])->name('admin.service.add');
        Route::post('admin/service/edit/{id}', [AdminController::class, 'EditService'])->name('admin.service.edit');
        Route::get('providers', [AdminController::class, 'providers'])->name('providers');
        Route::post('add-provider', [AdminController::class, 'addProvider'])->name('add_provider');
        Route::post('edit-provider/{id}', [AdminController::class, 'editProvider'])->name('edit_provider');
        Route::get('status-provider/{id}', [AdminController::class, 'statusProvider'])->name('status_provider');
        Route::get('/schemes', [SchemeController::class, 'index'])->name('schemes.index');
        Route::post('add-scheme-rule', [AdminController::class, 'addSchemeAndRule'])->name('add_scheme_rule');
        Route::get('edit-scheme/{id}', [AdminController::class, 'editScheme'])->name('edit_scheme');
        Route::get('edit-assigned-scheme/{id}', [AdminController::class, 'editAssignedScheme'])->name('edit_assign_scheme');
        Route::post('assign-scheme', [AdminController::class, 'assignSchemetoUser'])->name('assign_scheme');
        Route::post('update-user-assigned-scheme/{id}', [AdminController::class, 'updateAssignedSchemetoUser'])->name('update_user_assigned_scheme');
        Route::get('delete-assigned-scheme/{id}', [AdminController::class, 'deleteAssignedScheme'])->name('delete_assign_scheme');
        Route::post('update-scheme-rule/{id}', [AdminController::class, 'updateSchemeAndRule'])->name('update_scheme_rule');
        Route::get('/services/{serviceId}/providers', [UserController::class, 'getServiceProviders'])
            ->name('admin.services.providers');
        Route::post('service-request-approve-reject', [ServiceRequestController::class, 'approveRejectRequestService'])->name('service_request_approve_reject');

        // Assign user to support in admin routes
        Route::get('/user-assign-to-support', [AdminController::class, 'UserassigntoSupport'])->name('user_assign_to_support');
        Route::post('/assign-user-to-support', [AdminController::class, 'UserAssignedtoSupportuser'])->name('save_support_assignment');
        Route::get('/edit-support-assignment/{id}', [AdminController::class, 'editSupportAssignment'])->name('edit_support_assignment');
        Route::delete('delete-support-assignment/{id}', [AdminController::class, 'deleteSupportAssignment'])->name('delete_support_assignment');

        // Complain Report Route

    });
});

Route::group(['middleware' => ['isUser', 'logs', 'auth'], 'prefix' => 'user'], function () {
    Route::prefix('bbps-recharge')->group(function () {
        Route::post('getPlans/{operator_id}/{circle_id}/{plan_type?}', [BbpsRechargeController::class, 'getPlans'])->name('bbps.getPlans');

        Route::post('validateRecharge', [BbpsRechargeController::class, 'validateRecharge'])->name('bbps.validateRecharge');

        Route::post('status', [BbpsRechargeController::class, 'status'])->name('bbps.status');
        Route::post('mpin-auth', [BbpsRechargeController::class, 'mpinAuth'])->name('bbps.mpin_auth');
    });

    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');

    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');


    Route::post('completeProfile/{user_id}', [UserController::class, 'completeProfile'])->name('admin.complete_profile');
    // Service Related Route
    Route::group(['middleware' => ['isUserAccessPage']], function () {
        Route::get('/utility-service', [ServiceController::class, 'utilityService'])->name('utility_service');
        Route::get('/recharge-service', [ServiceController::class, 'rechargeService'])->name('recharge_service');
        Route::get('/banking-service', [ServiceController::class, 'bankingService'])->name('banking_service');
    });

    // Users Route
    Route::get('/transaction-status', [TransactionController::class, 'transactionStatus'])->name('transaction_status');
    Route::get('/transaction-complaint', [TransactionController::class, 'transactionComplaint'])->name('transaction_complaint');
    Route::post('add-ip-address', [UserController::class, 'addIpWhiteList'])->name('add_ip_address');
    Route::post('update-ip-address/{id}', [UserController::class, 'editIpWhiteList'])->name('update_ip_address');
    Route::get('status-ip-address/{id}', [UserController::class, 'statusIpWhiteList'])->name('status_ip_address');
    Route::get('delete-ip-address/{id}', [UserController::class, 'deleteIpWhiteList'])->name('delete_ip_address');
    Route::post('webhook-url/save', [UserController::class, 'WebHookUrl'])->name('web_hook_url');
    Route::post('/complaints', [TransactionController::class, 'store'])->name('complaints.store');
    Route::get('/complete-kyc', [UserController::class, 'redirectToKycPage'])->name('open.kyc.page');
    Route::get('/complaint-status', [TransactionController::class, 'complaintStatus'])->name('complaint_status');
    Route::post('/complaint-status/check', [TransactionController::class, 'checkComplaintStatus'])->name('complaint.status.check');
    Route::post('nsdl-initiated-payment', [UserController::class, 'initiateNsdlPayment'])->name('nsdl-initiatePayment');
    Route::post('/service-request', [ServiceRequestController::class, 'store'])
        ->name('service.request');
    // Route::post('/users/{id}/routing/save', [UserController::class, 'saveUserRouting'])
    //     ->name('admin.users.routing.save');
    Route::post('completeProfile/{user_id}', [UserController::class, 'completeProfile'])->name('admin.complete_profile');
    Route::post('generate-mpin', [UserController::class, 'generateMpin'])->name('generate_mpin');
    Route::post('/transaction-status-check', [TransactionController::class, 'transactionStatusCheck'])->name('transaction_status_check');



    // reseller routes
    // Route::get('reports', [LadgerController::class, 'reports'])->name('reseller_reports');
    // Route::get('services', [ServiceRequestController::class, 'enabledServices'])->name('enabled_services');

    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');
});


Route::group(['middleware' => ['auth']], function () {
    // Support User Route
    Route::prefix('support')->group(function () {

        // Route::get('/complaints-report', [SupportDashboardController::class, 'userComplaints'])->name('complaints_report');
        //         Route::get('/support-userlist', [SupportDashboardController::class, 'supportUserList'])->name('support_userlist');
        Route::get('complaints-report', [SupportDashboardController::class, 'userComplaints'])->name('complaints_report')->middleware('isSupport');
        Route::get('/support-userlist', [SupportDashboardController::class, 'supportUserList'])->name('support_userlist')->middleware('isSupport');
    });
    Route::post('/update-complaint-report/{id}', [ComplainReportController::class, 'updateComplaint'])->name('update_complaint_report');
    // Admin/User Common Routes 
    Route::post('change-password', [AuthController::class, 'passwordReset'])->name('admin.change_password');
    Route::get('profile/{user_id}', [AdminController::class, 'adminProfile'])->name('admin_profile');
    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
    Route::get('/complain-report', [ComplainReportController::class, 'complainReport'])->name('complain.report'); //common in admin and support user
    Route::get('reports/{type}', [ReportController::class, 'index'])->name('reports'); //common for transaction section report, banking, utility 
    Route::get('/ledger', [LadgerController::class, 'index'])->name('ladger.index');
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/transaction-report', [TransactionController::class, 'transaction_Report'])->name('transaction.report');
    Route::get('recharge/invoice/{id}', [TransactionController::class, 'downloadInvoice'])
        ->name('recharge.invoice.download');
    Route::get('services', [ServiceRequestController::class, 'enabledServices'])->name('enabled_services');


    Route::get('unauthrized', function () {
        return view('errors.401');
    })->name('unauthrized.page');

    // reseller routes
    Route::get('reports', [LadgerController::class, 'reports'])->name('reseller_reports');
    Route::get('services', [ServiceRequestController::class, 'enabledServices'])->name('enabled_services');
});


Route::group(['middleware' => ['auth'], 'prefix' => 'api-partner'], function () {
    Route::get('/dashboard', [HomeController::class, 'apiPartner'])->name('api.dashboard');

//     Route::get('reports/{type}', [ReportController::class, 'index'])->name('reseller.reports');

    // Route::get('ledger-reports', [LadgerController::class, 'reports'])->name('reseller_reports');

});


Route::group(['middleware' => ['auth', 'isSupport'], 'prefix' => 'support'], function () {
    Route::get('/dashboard', [HomeController::class, 'supportdashboard'])->name('support.dashboard');
});


Route::prefix('admin', function () {
    Route::get('me', [AuthController::class, 'me']);
});
