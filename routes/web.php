<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentDemoController;
use App\Http\Controllers\TestController;
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

Route::get('/payment-demo', [PaymentDemoController::class, 'index']);
Route::get('/payment-method', [PaymentDemoController::class, 'gcash']);