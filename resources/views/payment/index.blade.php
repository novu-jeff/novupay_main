@extends('layouts.payment')

@section('base')
    <div class="bg-color"></div>
    <div class="container">
        <div class="content">
            <h1 class="text-center">NOVULUTIONS' PAYMENT GATEWAY</h1>
            <div class="card shadow-lg">
                <h5>Banks</h5>
                <div class="banks mb-3">
                    <div class="bank" data-id="pm-dob-mbnk">
                        <img src="{{ asset('images/banks/metrobank.png') }}" alt="Metro Bank">
                    </div>
                    <div class="bank" data-id="pm-dob-bdo">
                        <img src="{{ asset('images/banks/bdo.png') }}" alt="BDO">
                    </div>
                    <div class="bank" data-id="pm-dob-bpi">
                        <img src="{{ asset('images/banks/bpi.png') }}" alt="BPI">
                    </div>
                    <div class="bank" data-id="pm-dob-lbnk">
                        <img src="{{ asset('images/banks/landbank.png') }}" alt="Land Bank">
                    </div>
                    <div class="bank" data-id="pm-dob-ubp">
                        <img src="{{ asset('images/banks/unionbank.png') }}" alt="Union Bank">
                    </div>
                </div>
                <h5>E-Wallet</h5>
                <div class="banks mb-4">
                    <div class="bank" data-id="maya">
                        <img src="{{ asset('images/banks/mayabank.png') }}" alt="Maya Bank">
                    </div>
                    <div class="bank" data-id="gcash">
                        <img src="{{ asset('images/other-banks/gcash.png') }}" alt="GCash">
                    </div>
                    <div class="bank" data-id="gcash-app">
                        <img src="{{ asset('images/other-banks/gcash.png') }}" alt="GCash">
                    </div>
                    <div class="bank" data-id="grabpay">
                        <img src="{{ asset('images/other-banks/grab pay.png') }}" alt="Grab Pay">
                    </div>
                </div>
        
                <h5>Bank Transfer</h5>
                <div class="banks">
                    <div class="bank" data-id="qrph">
                        <img src="{{ asset('images/other-banks/qrph.png') }}" alt="QR PH">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/pesonet.png') }}" alt="PESONet">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/instapay.png') }}" alt="InstaPay">
                    </div>
                </div>
        
                <div class="btn-container">
                    <form action="{{route('payment.merchants.store', ['transaction_id' => $transaction_id])}}" method="post">
                        @csrf
                        <input type="hidden" name="by_method" id="by_method">
                        <button class="btn btn-primary text-uppercase px-5 py-3 fw-bold">Proceed Payment</button>
                    </form>
                </div>
                <div class="pt-1 d-flex justify-content-center text-uppercase fw-bold text-muted" style="font-size: 12px;">
                    Powered by Novulutions Inc.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $(".bank").click(function () {
            const id = $(this).data('id') || ''; 
            console.log(id)
            $("#by_method").val(id);
            $(".bank").removeClass("selected");
            $(this).addClass("selected");
        });
    });
</script>
@endsection
