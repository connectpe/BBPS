<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\users\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommonController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Front.user-register');
})->name('home');


Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('signup', [AuthController::class, 'signup'])->name('signup');

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');


        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
    });

    Route::post('change-password', [AuthController::class, 'passwordReset'])->name('admin.change_password');
    Route::post('completeProfile', [AuthController::class, 'completeProfile'])->name('admin.complete_profile');

        // Admin  Related Route
    Route::get('profile', [AdminController::class, 'adminProfile'])->name('admin_profile');


    // Service Related Route
    Route::get('/utility-service', [ServiceController::class, 'utilityService'])->name('utility_service');
    Route::get('/recharge-service', [ServiceController::class, 'rechargeService'])->name('recharge_service');
    Route::get('/banking-service', [ServiceController::class, 'bankingService'])->name('banking_service');

    // Users Related Route 
    Route::get('/users', [UserController::class, 'bbpsUsers'])->name('users');
    Route::get('/users/ajax', [UserController::class, 'ajaxBbpsUsers'])->name('users_ajax');

    // Transaction Related Route 
    Route::get('/transaction-status', [TransactionController::class, 'transactionStatus'])->name('transaction_status');
    Route::get('/transaction-complaint', [TransactionController::class, 'transactionComplaint'])->name('transaction_complaint');
    Route::get('/complaint-status', [TransactionController::class, 'complaintStatus'])->name('complaint_status');

    Route::post('generate/client-credentials', [UserController::class, 'generateClientCredentials'])->name('generate_client_credentials');

    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);

});

Route::prefix('admin',function(){
	Route::get('me', [AuthController::class, 'me']);
});


