@extends('layouts.app')

@section('content')
<div style="text-align:center; padding:50px;">
    <h1 style="color:red;">❌ Payment Failed</h1>
    <p>Unfortunately, your payment could not be processed.</p>
    <p><strong>Reference ID:</strong> {{ $ref }}</p>
    <a href="/" style="color:#0069d9;">Try Again</a>
</div>
@endsection
