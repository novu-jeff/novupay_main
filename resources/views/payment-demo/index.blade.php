@extends('layouts.payment')

@section('base')
    <div class="bg-color"></div>
    <div class="container">
        <div class="content">
            <h1>NOVUPAY</h1>
            <div class="card shadow-lg">
                <h5>Banks</h5>
                <div class="banks mb-3">
                    <div class="bank">
                        <img src="{{ asset('images/banks/securitybank.png') }}" alt="Security Bank">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/banks/landbank.png') }}" alt="Land Bank">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/banks/bdo.png') }}" alt="BDO">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/banks/bpi.png') }}" alt="BPI">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/banks/aub.png') }}" alt="AUB">
                    </div>
                </div>
        
                <h5>E-Wallet</h5>
                <div class="banks mb-4">
                    <div class="bank">
                        <img src="{{ asset('images/banks/mayabank.png') }}" alt="Maya Bank">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/gcash.png') }}" alt="GCash">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/alipay.png') }}" alt="Alipay">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/shopee pay.png') }}" alt="Shopee Pay">
                    </div>
                    <div class="bank">
                        <img src="{{ asset('images/other-banks/grab pay.png') }}" alt="Grab Pay">
                    </div>
                </div>
        
                <h5>Bank Transfer</h5>
                <div class="banks">
                    <div class="bank">
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
                    <a class="btn btn-primary custom" href="#">Proceed Payment</a>
                </div>
                <div class="pt-1 d-flex justify-content-center">
                    Powered by Novulutions Inc
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
            $(".bank").removeClass("selected");

            $(this).addClass("selected");
        });

        $(".btn.custom").click(function (event) {
            event.preventDefault(); 

            let selectedBank = $(".bank.selected img").attr("alt");

            if (!selectedBank) {
                alert("Please select a payment method before proceeding.");
                return;
            }

            if (selectedBank !== 'GCash') {
                alert("Please select a different payment method. The merchant is not yet registered.");
                return;
            }

            const queryString = window.location.search;

            // Use URLSearchParams to extract the 'price' parameter
            const urlParams = new URLSearchParams(queryString);
            const price = urlParams.get('price');

            console.log(price);

            let paymentUrl = "/payment-method?bank=" + encodeURIComponent(selectedBank) + "&price=" + encodeURIComponent(price);
            window.location.href = paymentUrl;

        });
    });
</script>
@endsection
