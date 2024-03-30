<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthOtpController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\HeadsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\UserController;

Route::group(['middleware' => ['api']], function () {

    // Route for login with OTP
    Route::post('login', [AuthOtpController::class, 'loginWithOtp']);

    Route::post('logout', [AuthOtpController::class, 'logout']);

    // Route for adding and viewing expense labels
    Route::post('exp_label', [LabelController::class, 'addViewlabel']);

    // Route for adding and viewing expense heads
    Route::post('exp_head', [HeadsController::class, 'addViewHeads']);

    // Route for adding and viewing expenses
    Route::post('expenses', [ExpensesController::class, 'addViewExpenses']);

    // Route for adding and viewing users
    Route::post('user', [UserController::class, 'addViewUser']);

    // Route for retrieving user information (protected by Sanctum)
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

