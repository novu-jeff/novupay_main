<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentNavigationController;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\StaritaSampleCheckoutController;
use App\Http\Controllers\StaritaHostedCheckoutController;



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

Route::get('/payment-checkout/srwd/{reference_no}', [StaritaHostedCheckoutController::class, 'redirect'])
    ->name('payment.checkout.srwd');

Route::get('/payment-checkout/sample/srwd/{reference_no?}', [StaritaSampleCheckoutController::class, 'show'])
    ->name('payment.checkout.sample.srwd');

Route::get('/payment-expired/sample/srwd', function () {
    return view('payment.expired', [
        'reference_no' => 'SRWD-SAMPLE-000001',
        'expires_at'   => now()->subDays(5)->endOfDay()->format('Y-m-d H:i:s'),
        'utility'      => 'srwd',
    ]);
})->name('payment.expired.sample.srwd');

Route::get('/payment-expired/sample', function () {
    return view('payment.expired', [
        'reference_no' => 'NOVUPAY-SAMPLE-000001',
        'expires_at'   => now()->subDays(3)->endOfDay()->format('Y-m-d H:i:s'),
        'utility'      => request('utility'),
    ]);
})->name('payment.expired.sample');

Route::get('/payment-expired/{reference_no}', function ($reference_no) {
    return view('payment.expired', [
        'reference_no' => $reference_no,
        'expires_at'   => request('expires_at'),
        'utility'      => request('utility'),
    ]);
})->name('payment.expired');

Route::get('/payment-maintenance/{reference_no}', function ($reference_no) {
    $bill = null;
    try {
        $bill = \App\Models\StaritaBill::where('reference_no', $reference_no)->first();
    } catch (\Throwable $e) {
        report($e);
    }

    return view('payment.maintenance', [
        'pageTitle' => 'Online Payment Maintenance',
        'reference_no' => $reference_no,
        'account_no' => $bill->account_no ?? request('account_no'),
        'payor' => $bill->payor ?? request('payor'),
        'amount' => $bill->amount ?? request('amount'),
    ]);
})->name('payment.maintenance');

// Placeholder pages
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/demo', [HomeController::class, 'demo'])->name('demo');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/api-docs', [HomeController::class, 'apiDocs'])->name('api.docs');
Route::get('/services/novupay/whitepaper', function () {
    return redirect()->route('services.novupay.whitepaper');
});
Route::get('/services/novupay/white-paper', [HomeController::class, 'novupayWhitePaper'])
    ->name('services.novupay.whitepaper');
Route::get('/services/novupay/white-paper.docx', [HomeController::class, 'novupayWhitePaperDocx'])
    ->name('services.novupay.whitepaper.docx');
Route::get('/services/novupay/process-flows', function () {
    return redirect()->route('services.novupay.processflow');
});
Route::get('/services/novupay/process-flow', [HomeController::class, 'novupayProcessFlow'])
    ->name('services.novupay.processflow');
if (config('novupay.other_services_enabled')) {
    Route::get('/services/novustream/whitepaper', function () {
        return redirect()->route('services.novustream.whitepaper');
    });
    Route::get('/services/novustream/white-paper', [HomeController::class, 'novustreamWhitePaper'])
        ->name('services.novustream.whitepaper');
    Route::get('/services/novusurge/whitepaper', function () {
        return redirect()->route('services.novusurge.whitepaper');
    });
    Route::get('/services/novusurge/white-paper', [HomeController::class, 'novusurgeWhitePaper'])
        ->name('services.novusurge.whitepaper');
    Route::get('/services/novusurge/meter-inspection/whitepaper', function () {
        return redirect()->route('services.novusurge.meter-inspection.whitepaper');
    });
    Route::get('/services/novusurge/meter-inspection/white-paper', [HomeController::class, 'novusurgeMeterInspectionWhitePaper'])
        ->name('services.novusurge.meter-inspection.whitepaper');
} else {
    $redirectOtherServicesHome = fn () => redirect()->route('home');
    Route::get('/services/novustream/whitepaper', $redirectOtherServicesHome);
    Route::get('/services/novustream/white-paper', $redirectOtherServicesHome);
    Route::get('/services/novusurge/whitepaper', $redirectOtherServicesHome);
    Route::get('/services/novusurge/white-paper', $redirectOtherServicesHome);
    Route::get('/services/novusurge/meter-inspection/whitepaper', $redirectOtherServicesHome);
    Route::get('/services/novusurge/meter-inspection/white-paper', $redirectOtherServicesHome);
}
