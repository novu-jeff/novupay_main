@extends('layouts.payment')

@section('base')
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4 text-center" style="max-width: 520px; height:420px; width: 100%; border: none; border-radius: 24px">
            <img src="{{ asset('images/other-banks/gcash.png') }}" alt="GCash Logo" class="mb-3" width="180">
            <h2 class="text-primary fw-bold">GCash Payment</h2>
            <p class="text-muted">Secure and convenient way to pay</p>
            
            <div id="number-section">
                <input type="tel" style="margin-bottom: 20px; margin-top: 20px" id="gcash-number" class="form-control text-center py-2 fs-5 custom" placeholder="09XXXXXXXXX" maxlength="11">
                <button class="btn btn-primary custom w-100 mt-3 fw-bold py-2" id="next-btn">Next</button>
            </div>
            
            <div id="pin-section" class="mt-3" style="display: none;">
                <h5 class="mb-2">Enter your GCash PIN</h5>
                <div class="d-flex justify-content-center gap-2">
                    <input type="password" class="pin-input form-control text-center fw-bold" maxlength="1">
                    <input type="password" class="pin-input form-control text-center fw-bold" maxlength="1">
                    <input type="password" class="pin-input form-control text-center fw-bold" maxlength="1">
                    <input type="password" class="pin-input form-control text-center fw-bold" maxlength="1">
                </div>
                <button class="btn btn-success w-100 mt-3 fw-bold py-2 custom" id="pay-btn" disabled>Proceed Payment</button>
            </div>
            
            <div id="processing-section" class="mt-3 text-center" style="display: none;">
                <h5 class="fw-bold">Processing Payment...</h5>
                <div class="spinner-border text-primary mt-2" role="status"></div>
            </div>
            
            <div id="success-section" class="mt-3 text-center" style="display: none;">
                <h3 class="text-success fw-bold">Payment Successful!</h3>
                <p class="text-muted">Your GCash payment has been processed successfully.</p>
                <a href="/payment-demo" class="btn btn-primary custom fw-bold py-2">Return to Home</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#next-btn").click(function () {
            let number = $("#gcash-number").val().trim();
            if (!/^09\d{9}$/.test(number)) {
                alert("Please enter a valid 11-digit GCash number starting with 09.");
                return;
            }
            $("#number-section").fadeOut(200, function () {
                $("#pin-section").fadeIn(200);
            });
        });

        $(".pin-input").on("input", function () {
            let pin = "";
            $(".pin-input").each(function () {
                pin += $(this).val();
            });
            $("#pay-btn").prop("disabled", pin.length !== 4);
            if ($(this).val() !== "" && $(this).next(".pin-input").length) {
                $(this).next(".pin-input").focus();
            }
        });

        $("#pay-btn").click(function () {
            $("#pin-section").fadeOut(200, function () {
                $("#processing-section").fadeIn(200);
            });
            setTimeout(() => {
                $("#processing-section").fadeOut(200, function () {
                    $("#success-section").fadeIn(200);
                });
            }, 3000);
        });
    });
</script>

<style>
    .pin-input {
        width: 50px;
        height: 50px;
        font-size: 24px;
        text-align: center;
        border: 2px solid #007bff;
        border-radius: 8px;
    }
    .pin-input:focus {
        border-color: #0056b3;
        outline: none;
    }
    body {
        background-color: #0056b3 !important;
    }
</style>
@endsection