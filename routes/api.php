<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function() {
    Route::post('/save/transaction', [PaymentController::class, 'saveTransaction']);
    Route::get('/pay', [PaymentController::class, 'pay'])
        ->name('payment.index');
    Route::post('/pay', [PaymentController::class, 'pay'])
        ->name('payment.pay');
    Route::post('/status/{operation_id}', [PaymentController::class, 'status'])
        ->name('payment.status');
    Route::get('/callback/{operation_id}', [PaymentController::class, 'callback'])
        ->name('payment.callback');

});

