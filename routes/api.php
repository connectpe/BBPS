<?php

use App\Http\Controllers\BbpsRechargeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobikwikController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\DocumentVerificationController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('testToken', [BbpsRechargeController::class, 'testToken'])->name('bbps.test_token');
// Route::get('getPlans/{operator_id}/{circle_id}/{plan_type?}', [BbpsRechargeController::class, 'getPlans'])->name('bbps.getPlans');
Route::post('balance', [BbpsRechargeController::class, 'balance'])->name('bbps.balance');
Route::post('postpaid-villbill', [BbpsRechargeController::class, 'postpaidVillBill'])->name('bbps.postpaid_villBill');



Route::group(['middleware' => ['logs']], function () {
    Route::prefix('bbps')->group(function () {

        Route::post('getplans/{provider}/{circle}/{operator}/{plan_type?}', [MobikwikController::class, 'getplans']);
        Route::post('balance/{type}', [MobikwikController::class, 'getBalance'])->name('Mobikwik.balanace');
        Route::post('payment/{type}', [MobikwikController::class, 'mobikwikPayment'])->name('Mobikwik.payment');
        Route::post('status/{type}', [MobikwikController::class, 'mobikwikStatus'])->name('Mobikwik.status');
        Route::post('recharge-validation/{type}', [MobikwikController::class, 'validateRecharge'])->name('Mobikwik.recharge.validation');
        Route::post('fetch-bill/{type}', [MobikwikController::class, 'fetchPostpaidBill'])->name('fetch.postpaid.bill');
    });

    Route::post('callback/{type}', [CallbackController::class, 'handle'])->name('api.callback');
});

Route::group(['middleware'=> ['logs','auth'],'prefix'=>'document'],function(){
    Route::post('verify-pan',[DocumentVerificationController::class,'panVerify'])->name('pan.verify');
    Route::post('verify-account',[DocumentVerificationController::class,'VerifyAccountDetails'])->name('bank.account.verify');
    Route::post('verify-cin',[DocumentVerificationController::class,'verifyCinNumber'])->name('cin.verify');
    Route::post('verify-gstin',[DocumentVerificationController::class,'verifyGstinNumber'])->name('gstin.verify');
});



// Route::post('validateRecharge', [BbpsRechargeController::class, 'validateRecharge'])->name('bbps.validateRecharge');
