<?php

use App\Http\Controllers\BbpsRechargeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobikwikController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::prefix('recharge')->group(function () {
//     Route::post('/get-plans', [BbpsRechargeController::class, 'getPlans']);
// });

// Route::post('generate-token',[BbpsRechargeController::class,'generateToken'])->name('bbps.generate_token');
Route::post('testToken', [BbpsRechargeController::class, 'testToken'])->name('bbps.test_token');
// Route::get('getPlans/{operator_id}/{circle_id}/{plan_type?}', [BbpsRechargeController::class, 'getPlans'])->name('bbps.getPlans');
Route::post('balance', [BbpsRechargeController::class, 'balance'])->name('bbps.balance');


Route::group(['middleware' => ['logs']], function () {
    Route::prefix('bbps')->group(function () {
        Route::post('getplans/{provider}/{circle}/{operator}/{plan_type?}',[MobikwikController::class, 'getplans']);
        Route::post('balance/{type}',[MobikwikController::class, 'getBalance']);
        Route::post('recharge-validation/{type}',[MobikwikController::class, 'validateRecharge']);
    });
});

