<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentNavigationController;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\PaymentPageController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'home'])
    ->name('home');

Route::get('/page/{status}', [HomeController::class, 'payment_status'])
    ->name('home.status');

Route::get('/payment/merchants/{transaction_id}', [PaymentNavigationController::class, 'show'])
    ->name('payment.merchants.show');
Route::get('/payment/merchants/pay/{transaction_id}/{operation_id}', [PaymentNavigationController::class, 'pay'])
    ->name('payment.merchants.pay');

Route::post('/payment/merchants/{transaction_id}', [PaymentNavigationController::class, 'store'])
    ->name('payment.merchants.store');

Route::post('/payment/choose/other/{payment_id}/{reference_no}', [PaymentNavigationController::class, 'choose_other'])
    ->name('payment.other.merchants');
    

// Route::post('/payment-request/api', [PaymentRequestController::class, 'apiCreateHitpayPaymentRequest']);



Route::get('/payment-confirmation', [PaymentPageController::class, 'confirmation'])->name('payment.paid');
Route::get('/payment-failed', [PaymentPageController::class, 'failed'])->name('payment.failed');
Route::get('/payment-pending', [PaymentPageController::class, 'pending'])->name('payment.pending');
// routes/web.php
Route::get('/payment/pdf/{id}', [PaymentPageController::class, 'downloadPdf'])->name('payment.pdf');

Route::get('/payment-expired/{reference_no}', function ($reference_no) {
    return view('payment.expired', [
        'reference_no' => $reference_no,
        'expires_at'   => request('expires_at')
    ]);
})->name('payment.expired');

// Placeholder pages
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/demo', [HomeController::class, 'demo'])->name('demo');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/api-docs', [HomeController::class, 'apiDocs'])->name('api.docs');
