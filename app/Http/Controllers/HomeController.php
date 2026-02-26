<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{

    public function home() {
        $banks = File::files(public_path('images/banks'));
        $other_banks = File::files(public_path('images/other-banks'));

        $partners = [
            'banks' => $banks,
            'other_banks' => $other_banks
        ];

        return view('home', compact('partners'));
    }

    public function payment_status(string $status) {

        $allowed = ['success', 'error'];

        if(!in_array($status, $allowed)) {
            return redirect()->route('home');
        }

        $config = [
            'success' => [
                'status' => 'Succeed',
                'title' => 'Payment Sucess!',
                'message' => 'Your payment has been successfully processed.',
                'reference_no' => '000123232',
                'amount' => 'PHP 10,000.00',
                'date' => Carbon::now()->format('F d, Y || h:iA'),
            ],
            'error' => [
                'status' => 'failed',
                'title' => 'Payment Failed!',
                'message' => 'An error occured while processing your payment. Please try again!',
                'amount' => 'PHP 10,000.00',
            ]
        ];

        $data = $config[$status];

        return view('home.status', compact('data'));
    }

    public function contact() {
        return view('pages.contact');
    }

    public function demo() {
        return view('pages.demo');
    }

    public function about() {
        return view('pages.about');
    }

    public function apiDocs() {
        return view('pages.api-docs');
    }
}
