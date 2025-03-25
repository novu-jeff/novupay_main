<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentNavigationController;
use Illuminate\Support\Facades\Route;

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

Route::post('/payment/merchants/{transaction_id}', [PaymentNavigationController::class, 'store'])
    ->name('payment.merchants.store');

Route::post('/payment/choose/other/{payment_id}/{reference_no}', [PaymentNavigationController::class, 'choose_other'])
    ->name('payment.other.merchants');
    