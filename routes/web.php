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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Front.user-register');
})->name('home');

Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('signup', [AuthController::class, 'signup'])->name('admin.signup');


// 'logs' : Middleware for the logs.

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        // Route::get('/dashboard', function () {
        //     return view('dashboard');
        // })->name('dashboard');

        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle.user');
        Route::post('user-status-change', [AdminController::class, 'changeUserStatus'])->name('admin.user_status.change');
        Route::post('add-service', [AdminController::class, 'addService'])->name('admin.add_service');
        Route::put('edit-service/{service_id}', [AdminController::class, 'editService'])->name('admin.edit_service');

        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle');
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');


        // Support member routes here 

        Route::get('/support-details', [AdminController::class, 'supportdetails'])->name('support_details');
        Route::post('/add-s-member', [AdminController::class, 'addSupportMember'])->name('add.support.member');
        Route::get('/get-s-member/{id}', [AdminController::class, 'getSupportMember'])->name('get.support.member');
        Route::post('/edit-s-member/{user_id}', [AdminController::class, 'editSupportMember'])->name('edit.support.member');
    });

    // RECHARGE RELATED ROUTE 8010801087
    Route::prefix('bbps-recharge')->group(function () {
        Route::post('getPlans/{operator_id}/{circle_id}/{plan_type?}', [BbpsRechargeController::class, 'getPlans'])->name('bbps.getPlans');
        // Route::post('genrate-token',[BbpsRechargeController::class,'generateToken'])->name('bbps.generate_token');

        // Route::post('balance', [BbpsRechargeController::class, 'balance'])->name('bbps.balance');
        Route::post('validateRecharge', [BbpsRechargeController::class, 'validateRecharge'])->name('bbps.validateRecharge');
        // Route::post('payment', [BbpsRechargeController::class, 'payment'])->name('bbps.payment');
        Route::post('status', [BbpsRechargeController::class, 'status'])->name('bbps.status');
        Route::post('mpin-auth', [BbpsRechargeController::class, 'mpinAuth'])->name('bbps.mpin_auth');
        Route::post('postpaid-villbill', [BbpsRechargeController::class, 'postpaidVillBill'])->name('bbps.postpaid_villBill');
    });

    Route::post('change-password', [AuthController::class, 'passwordReset'])->name('admin.change_password');

    Route::post('completeProfile/{user_id}', [UserController::class, 'completeProfile'])->name('admin.complete_profile');

    // Admin  Related Route
    Route::get('profile/{user_id}', [AdminController::class, 'adminProfile'])->name('admin_profile');

    // Service Related Route
    Route::get('/utility-service', [ServiceController::class, 'utilityService'])->name('utility_service');
    Route::get('/recharge-service', [ServiceController::class, 'rechargeService'])->name('recharge_service');
    Route::get('/banking-service', [ServiceController::class, 'bankingService'])->name('banking_service');
    Route::get('our-services', [ServiceController::class, 'ourService'])->name('our_servicess');
    Route::post('/admin/service/add', [AdminController::class, 'AddService'])
        ->name('admin.service.add');
    Route::post('admin/service/edit/{id}', [AdminController::class, 'EditService'])
        ->name('admin.service.edit');

    // Provider Related Route
    Route::get('providers', [AdminController::class, 'providers'])->name('providers');
    Route::post('add-provider', [AdminController::class, 'addProvider'])->name('add_provider');
    Route::post('edit-provider/{id}', [AdminController::class, 'editProvider'])->name('edit_provider');
    Route::get('status-provider/{id}', [AdminController::class, 'statusProvider'])->name('status_provider');

    // Route::prefix('recharge')->group(function () {
    //     Route::post('/get-plans', [BbpsRechargeController::class, 'getPlans']);
    // });

    Route::get('services', [ServiceRequestController::class, 'enabledServices'])->name('enabled_services');
    Route::get('request-services', [ServiceRequestController::class, 'index'])->name('request_services');
    Route::post('active-user-service-status', [ServiceController::class, 'activeUserService'])->name('active_user_service_status');

    // Users Related Route
    Route::get('/users', [UserController::class, 'bbpsUsers'])->name('users');

    Route::get('reports/{type}', [ReportController::class, 'index'])->name('reports');
    Route::get('reports', [LadgerController::class, 'reports'])->name('reseller_reports');

    // Route::get('recharge-report', [ReportController::class, 'RechargeReport'])->name('recharge_report');
    // Route::get('banking-report', [ReportController::class, 'BankingTransactionReport'])->name('banking_report');
    // Route::get('utility-report', [ReportController::class, 'UtilityTransactionReport'])->name('utility_report');

    Route::get('/view-user/{id}', [UserController::class, 'viewSingleUsers'])->name('view_user');

    // Transaction Related Route
    Route::get('/transaction-status', [TransactionController::class, 'transactionStatus'])->name('transaction_status');
    Route::get('/transaction-complaint', [TransactionController::class, 'transactionComplaint'])->name('transaction_complaint');
    Route::post('/complaints', [TransactionController::class, 'store'])->name('complaints.store');
    Route::get('/complaint-status', [TransactionController::class, 'complaintStatus'])->name('complaint_status');
    Route::post('/complaint-status/check', [TransactionController::class, 'checkComplaintStatus'])->name('complaint.status.check');
    Route::get('/transaction-report', [TransactionController::class, 'transaction_Report'])->name('transaction.report');


    // Complain Report Route
    Route::get('/complain-report', [ComplainReportController::class, 'complainReport'])->name('complain.report');
    Route::post('/update-complaint-report/{id}', [ComplainReportController::class, 'updateComplaint'])->name('update_complaint_report');



    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');

    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
    Route::post('/service-request', [ServiceRequestController::class, 'store'])
        ->name('service.request');
    Route::post('service-request-approve-reject', [ServiceRequestController::class, 'approveRejectRequestService'])->name('service_request_approve_reject');

    // ladger  Route
    Route::get('/ledger', [LadgerController::class, 'index'])->name('ladger.index');


    Route::get('/services/{serviceId}/providers', [UserController::class, 'getServiceProviders'])
        ->name('admin.services.providers');

    Route::post('/users/{id}/routing/save', [UserController::class, 'saveUserRouting'])
        ->name('admin.users.routing.save');

    // Api Log Related Route
    Route::get('api-log', [UserController::class, 'ApiLog'])->name('api_log');

    Route::post('/users/{id}/routing/save', [UserController::class, 'saveUserRouting'])
        ->name('admin.users.routing.save');

    // Scheme Report Route
    Route::get('/schemes', [SchemeController::class, 'index'])->name('schemes.index');

    // Scheme Related Route
    Route::post('add-scheme-rule', [AdminController::class, 'addSchemeAndRule'])->name('add_scheme_rule');
    // Scheme ka data fetch karne ke liye (Edit Modal ke liye)
    Route::get('edit-scheme/{id}', [AdminController::class, 'editScheme'])->name('edit_scheme');



    Route::get('edit-assigned-scheme/{id}', [AdminController::class, 'editAssignedScheme']);
    Route::post('assign-scheme', [AdminController::class, 'assignSchemetoUser'])->name('assign_scheme');
    Route::post('update-user-assigned-scheme/{id}', [AdminController::class, 'updateAssignedSchemetoUser'])->name('update_user_assigned_scheme');
    Route::get('delete-assigned-scheme/{id}', [AdminController::class, 'deleteAssignedScheme']);
    Route::post('update-scheme-rule/{id}', [AdminController::class, 'updateSchemeAndRule'])->name('update_scheme_rule');


    // support panel route
    Route::get('/support-userlist', [SupportDashboardController::class, 'supportUserList'])->name('support_userlist');


    // assign user to support 
    Route::get('/user-assign-to-support', [AdminController::class, 'UserassigntoSupport'])->name('user_assign_to_support');
    Route::post('/assign-user-to-support', [AdminController::class, 'UserAssignedtoSupportuser'])->name('save_support_assignment');
    Route::get('/edit-support-assignment/{id}', [AdminController::class, 'editSupportAssignment']);
    Route::delete('delete-support-assignment/{id}', [AdminController::class, 'deleteSupportAssignment']);

    Route::prefix('support')->group(function () {
        Route::get('complaints-report', [SupportDashboardController::class, 'userComplaints'])->name('complaints_report');
    });


    Route::post('add-ip-address', [UserController::class, 'addIpWhiteList'])->name('add_ip_address');
});

Route::prefix('admin', function () {
    Route::get('me', [AuthController::class, 'me']);
});
