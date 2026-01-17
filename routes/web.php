<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');




Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {

        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');


        Route::post('change-password',[AuthController::class,'passwordReset'])->name('password.reset');

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

    });
    
});

Route::prefix('admin',function(){
	Route::get('me', [AuthController::class, 'me']);
});


