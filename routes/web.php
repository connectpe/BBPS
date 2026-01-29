<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BbpsRechargeController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\users\ReportController;
use App\Http\Controllers\users\UserController;
use App\Http\Controllers\LadgerController;
use App\Http\Controllers\BbpsRechargeController;
use App\Http\Controllers\ComplainReportController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Front.user-register');
})->name('home');

Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('signup', [AuthController::class, 'signup'])->name('admin.signup');

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Route::get('/dashboard', function () {
        //     return view('dashboard');
        // })->name('dashboard');

        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle.user');
        Route::post('user-status-change', [AdminController::class, 'changeUserStatus'])->name('admin.user_status.change');
        Route::post('add-service', [AdminController::class, 'AddService'])->name('admin.add_service');
        Route::put('edit-service/{service_id}', [AdminController::class, 'EditService'])->name('admin.edit_service');

        Route::post('servicetoggle', [AdminController::class, 'disableUserService'])->name('admin.service_toggle');

        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
    });

    // RECHARGE RELATED ROUTE 8010801087
    Route::prefix('bbps-recharge')->group(function () {
        Route::post('genrate-token',[BbpsRechargeController::class,'generateToken'])->name('bbps.generate_token');
        Route::get('getPlans',[BbpsRechargeController::class,'getPlans'])->name('bbps.getPlans');
        Route::post('balance',[BbpsRechargeController::class,'balance'])->name('bbps.balance');
        Route::post('validateRecharge',[BbpsRechargeController::class,'validateRecharge'])->name('bbps.validateRecharge');
        Route::post('payment',[BbpsRechargeController::class,'payment'])->name('bbps.payment');
        Route::post('status',[BbpsRechargeController::class,'status'])->name('bbps.status');
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

        
    Route::prefix('recharge')->group(function () {
        Route::post('/get-plans', [BbpsRechargeController::class, 'getPlans']);
    });



    Route::get('services', [ServiceRequestController::class, 'enabledServices'])->name('enabled_services');

    Route::get('request-services', [ServiceRequestController::class, 'index'])->name('request_services');

    // Users Related Route
    Route::get('/users', [UserController::class, 'bbpsUsers'])->name('users');

    Route::get('reports/{type}', [ReportController::class, 'index'])->name('reports');


    // Route::get('recharge-report', [ReportController::class, 'RechargeReport'])->name('recharge_report');
    // Route::get('banking-report', [ReportController::class, 'BankingTransactionReport'])->name('banking_report');
    // Route::get('utility-report', [ReportController::class, 'UtilityTransactionReport'])->name('utility_report');

    Route::get('/view-user/{id}', [UserController::class, 'viewSingleUsers'])->name('view_user');

    // Transaction Related Route
    Route::get('/transaction-status', [TransactionController::class, 'transactionStatus'])->name('transaction_status');
    Route::get('/transaction-complaint', [TransactionController::class, 'transactionComplaint'])->name('transaction_complaint');
     Route::post('/complaints', [TransactionController::class, 'store'])->name('complaints.store');
    Route::get('/complaint-status', [TransactionController::class, 'complaintStatus'])->name('complaint_status');
    Route::post('/complaint-status/check', [TransactionController::class, 'checkComplaintStatus'])
    ->name('complaint.status.check');
    Route::get('/transaction-report', [TransactionController::class, 'transaction_Report'])->name('transaction.report');

    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');

    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
    Route::post('/service-request', [ServiceRequestController::class, 'store'])
        ->name('service.request');
    Route::post('/service-request/{id}/approve', [ServiceRequestController::class, 'approve'])->name('service.approve');

    // ladger  Route
    Route::get('/ledger', [LadgerController::class, 'index'])->name('ladger.index');

    // Complain Report Route
    Route::get('/complain-report', [ComplainReportController::class, 'complainReport'])->name('complain.report');
        Route::post('/complain-report/{id}/update', [ComplainReportController::class, 'updateComplaint'])
        ->name('complain.update');
});

Route::prefix('admin', function () {
    Route::get('me', [AuthController::class, 'me']);
});
