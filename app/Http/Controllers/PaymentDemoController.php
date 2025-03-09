<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentDemoController extends Controller
{
    public function index()
    {
        return view('payment-demo.index');
    }

    public function gcash()
    {
        return view('payment-demo.e-wallet.gcash');
    }
}
