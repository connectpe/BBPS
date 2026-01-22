<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\users\ReportController;
use App\Http\Controllers\users\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Front.user-register');
})->name('home');

Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('signup', [AuthController::class, 'signup'])->name('admin.signup');

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
    });

    Route::post('change-password', [AuthController::class, 'passwordReset'])->name('admin.change_password');
    Route::post('completeProfile', [UserController::class, 'completeProfile'])->name('admin.complete_profile');

    // Admin  Related Route
    Route::get('profile', [AdminController::class, 'adminProfile'])->name('admin_profile');

    // Service Related Route
    Route::get('/utility-service', [ServiceController::class, 'utilityService'])->name('utility_service');
    Route::get('/recharge-service', [ServiceController::class, 'rechargeService'])->name('recharge_service');
    Route::get('/banking-service', [ServiceController::class, 'bankingService'])->name('banking_service');
    Route::get('our-services', [ServiceController::class, 'ourService'])->name('our_servicess');


    // Users Related Route
    Route::get('/users', [UserController::class, 'bbpsUsers'])->name('users');
    Route::get('recharge-report', [ReportController::class, 'RechargeReport'])->name('recharge_report');
    Route::get('banking-report',[ReportController::class,'BankingTransactionReport'])->name('banking_report');
    Route::get('utility-report',[ReportController::class,'UtilityTransactionReport'])->name('utility_report');

    Route::get('/view-user/{id}', [UserController::class, 'viewSingleUsers'])->name('view_user');

    // Transaction Related Route
    Route::get('/transaction-status', [TransactionController::class, 'transactionStatus'])->name('transaction_status');
    Route::get('/transaction-complaint', [TransactionController::class, 'transactionComplaint'])->name('transaction_complaint');
    Route::get('/complaint-status', [TransactionController::class, 'complaintStatus'])->name('complaint_status');
    Route::get('/transaction-report', [TransactionController::class, 'transaction_Report'])->name('transaction.report');

    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');

    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
});

Route::prefix('admin', function () {
    Route::get('me', [AuthController::class, 'me']);
});
