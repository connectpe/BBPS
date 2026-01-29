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
