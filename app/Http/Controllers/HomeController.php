<?php

namespace App\Http\Controllers;

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

        $allowed = ['success', 'error', 'pending'];

        if(!in_array($status, $allowed)) {
            return redirect()->route('home');
        }

        return view('home.status', compact('status'));
    }
}
