<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\KaelcoPaymentController;
use App\Http\Controllers\KaelcoPaymentControllerTest;
use App\Http\Controllers\NovuStreamStaritaPaymentController;
use App\Http\Controllers\NovuStreamMorongPaymentController;
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



// Route::post('/payment-request', [PaymentRequestController::class, 'apiCreateHitpayPaymentRequest']);
Route::post('/payment-request/api', [PaymentRequestController::class, 'apiCreateHitpayPaymentRequest']);

// Route::post('/payment-request', [PaymentRequestController::class, 'apiCreateHitpayPaymentRequest']);

Route::middleware('api.key')->group(function () {
    Route::post('/payment-request', [PaymentRequestController::class, 'apiCreateHitpayPaymentRequest']);
});

/**
 * 🔹 Consumer QR Scan → Novupay (browser GET)
 * Publicly accessible — no auth, no key
 */
Route::get('/payment-request', [PaymentRequestController::class, 'handleQr'])
    ->withoutMiddleware(['api.key', 'auth:sanctum']);

/**
 * 🔹 HitPay → Novupay Webhook & Redirect
 */
Route::post('/payment/webhook', [PaymentWebhookController::class, 'handleWebhook']);
Route::get('/payment/redirect', [PaymentWebhookController::class, 'handleRedirect']);


// Kaelco Payment Request Route
Route::post('/kaelco/payment-request', [KaelcoPaymentController::class, 'receiveEncrypted']);
Route::post('/kaelco/payment-request/test', [KaelcoPaymentControllerTest::class, 'receiveEncrypted']);


Route::get('/novustream/starita/payment-request', [NovuStreamStaritaPaymentController::class, 'handle']);
Route::get('/novustream/morong/payment-request', [NovuStreamMorongPaymentController::class, 'handle']);
