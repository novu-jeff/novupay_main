<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentDemoController extends Controller
{
    public function index(Request $request)
    {
        $price = $request->query('price') ?? 0;
        $reference = $request->query('price') ?? 0;
        return view('payment-demo.index');
    }

    public function gcash(Request $request)
    {
        $price = $request->query('price') ?? 0;
        return view('payment-demo.e-wallet.gcash', compact('price'));
    }
}
