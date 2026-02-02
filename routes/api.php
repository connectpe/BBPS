<?php

use App\Http\Controllers\BbpsRechargeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('validateRecharge', [BbpsRechargeController::class, 'validateRecharge'])->name('bbps.validateRecharge');

