@extends('layouts.payment')

@section('base')
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="text-center p-4" style="max-width: 480px;">
            <h1 class="h4 mb-3">Online payment unavailable</h1>
            <p class="mb-2">
                Cannot do online payment for now, please try again later.
            </p>
            @if(!empty($reference_no))
                <p class="text-muted mb-4" style="font-size: 0.9rem;">
                    Reference No: <strong>{{ $reference_no }}</strong>
                </p>
            @endif
        </div>
    </div>
@endsection

