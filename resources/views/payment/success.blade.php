@extends('layouts.app')

@section('content')
<div style="text-align:center; padding:50px;">
    <h1 style="color:green;">✅ Payment Successful</h1>
    <p>Thank you for your payment!</p>
    @if($transaction)
        <p><strong>Reference No:</strong> {{ $transaction->reference_no }}</p>
        <p><strong>Amount Paid:</strong> ₱{{ number_format($transaction->amount, 2) }}</p>
    @else
        <p><strong>Reference ID:</strong> {{ $ref }}</p>
    @endif
    <a href="/" style="color:#0069d9;">Return to Home</a>
</div>
@endsection
