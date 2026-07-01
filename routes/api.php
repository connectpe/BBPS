<?php

use App\Http\Controllers\BbpsRechargeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobikwikController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\DocumentVerification;
use App\Http\Controllers\Api\PayinCheckStatusController;
use App\Http\Controllers\Api\PayinOrdersController;
use App\Http\Controllers\Api\PayinCallbacksController;
use App\Http\Controllers\ServiceCostController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PayoutOrderController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('testToken', [BbpsRechargeController::class, 'testToken'])->name('bbps.test_token');
// Route::get('getPlans/{operator_id}/{circle_id}/{plan_type?}', [BbpsRechargeController::class, 'getPlans'])->name('bbps.getPlans');
Route::post('balance', [BbpsRechargeController::class, 'balance'])->name('bbps.balance');
Route::post('postpaid-villbill', [BbpsRechargeController::class, 'postpaidVillBill'])->name('bbps.postpaid_villBill');



Route::group(['middleware' => ['logs', 'basicAuth']], function () {
    Route::prefix('mobile-recharge')->group(function () {

        Route::post('getplans/{circle}/{operator}/{plan_type?}', [MobikwikController::class, 'getplans']);
        Route::post('balance', [MobikwikController::class, 'getBalance'])->name('Mobikwik.balanace');
        Route::post('retailerPayment', [MobikwikController::class, 'retailerPayment'])->name('Mobikwik.payment');
        Route::post('status/{type}', [MobikwikController::class, 'mobikwikStatus'])->name('Mobikwik.status');
        Route::post('recharge-validation', [MobikwikController::class, 'validateRecharge'])->name('Mobikwik.recharge.validation');
        Route::post('fetch-bill/{type}', [MobikwikController::class, 'fetchPostpaidBill'])->name('fetch.postpaid.bill');
    });

    Route::post('callback/{type}', [CallbackController::class, 'handle'])->name('api.callback');
});

Route::prefix('payin')->group(function () {
    Route::post('/orders', [PayinOrdersController::class, 'createOrders']);
    Route::post('/callbacks/{type}', [PayinCallbacksController::class, 'callbacks']);
    Route::post('/checkStatus', [PayinCheckStatusController::class, 'checkStatus']);
});

Route::group(['middleware' => ['logs', 'basicAuth']], function () {
    Route::prefix('payout')->group(function () {
        Route::post('/contacts', [ContactController::class, 'createContact']);
        Route::post('/orders', [PayoutOrderController::class, 'createOrder']);
    });
});

Route::group(['middleware' => ['logs'], 'prefix' => 'verification'], function () {
    Route::post('pan', [DocumentVerification::class, 'panVerify'])->name('pan_verify');
    Route::post('bank-account', [DocumentVerification::class, 'bankAccountVerify'])->name('bank_account_verify');
    Route::post('cin', [DocumentVerification::class, 'cinVerify'])->name('cin_verify');
    Route::post('gstin', [DocumentVerification::class, 'gstinVerify'])->name('gstin_verify');
    Route::post('ifsc', [DocumentVerification::class, 'ifscVerify'])->name('ifsc_verify');
    Route::post('aadhar-masking', [DocumentVerification::class, 'aadharMaskingVerify'])->name('aadhar_masking_verify');
    // Route::post('initiate-video-link', [DocumentVerificationController::class, 'initiateVideoKyc'])->name('initiate.video.link');

});




// Route::post('validateRecharge', [BbpsRechargeController::class, 'validateRecharge'])->name('bbps.validateRecharge');
