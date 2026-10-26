@extends('layouts.status')

@section('base')
<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">
            <div class="top">
                <div class="icon error">
                    <box-icon color='white' name='error'></box-icon>
                </div>
                <div class="header">
                    <h5>Checkout Unavailable</h5>
                    <p>{{ $message ?? 'Unable to start online payment right now. Please try again in a few minutes or pay at the Sta. Rita Water District office.' }}</p>
                </div>
            </div>

            <hr>

            <div class="mid">
                <h6>Bill Details</h6>
                <div class="details">
                    <div class="items">
                        <div>Reference No:</div>
                        <div>{{ $reference_no ?? '-' }}</div>
                    </div>
                    <div class="items">
                        <div>Status:</div>
                        <div>Unavailable</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
