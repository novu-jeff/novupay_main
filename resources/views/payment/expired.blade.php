@extends('layouts.status')

@section('base')

<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">

            {{-- TOP HEADER --}}
            <div class="top">
                <div class="icon error">
                    <box-icon color='white' name='x'></box-icon>
                </div>

                <div class="header">
                    <h5>Payment Not Available</h5>

                    <p>
                        The due date for this bill has already expired.
                        Online payment is no longer allowed.
                    </p>

                    <h3 class="text-danger">Expired</h3>
                </div>
            </div>

            <hr>

            {{-- PAYMENT DETAILS --}}
            <div class="mid">
                <h6>Details :</h6>
                <div class="details">

                    <div class="items">
                        <div>Reference No:</div>
                        <div>{{ $reference_no ?? '-' }}</div>
                    </div>

                    @if(!empty($expires_at))
                        <div class="items">
                            <div>Expired On:</div>
                            <div class="text-danger fw-bold">
                                {{ \Carbon\Carbon::parse($expires_at)->format('M d, Y h:i A') }}
                                (Expired)
                            </div>
                        </div>
                    @endif

                </div>

                <hr>

                <div class="text-center mt-3">
                    <div class="fw-bold text-muted" style="font-size: 15px;">
                        To proceed with payment, please visit any authorized
                        collection office or pay directly over the counter.
                    </div>
                </div>
            </div>
        </div>

        {{-- SUPPORT BOX --}}
        <div class="wrapper">
            <div class="others">
                <div class="d-flex justify-content-between m-auto align-items-center gap-3">
                    <div class="d-md-flex align-items-center gap-3">
                        <div class="icon d-none d-md-flex">
                            <box-icon color='white' animation='tada' name='question-mark'></box-icon>
                        </div>
                        <div class="d-block">
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Need assistance?
                            </div>
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Contact our support team.
                            </div>
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Email: <a href="mailto:support@novupay.ph">support@novupay.ph</a>
                            </div>
                        </div>
                    </div>
                    <div class="icon">
                        <box-icon name='chevron-right'></box-icon>
                    </div>
                </div>
            </div>
        </div>

        {{-- GO BACK BUTTON --}}
        <div class="wrapper">
            <div class="bottom">
                <button onclick="window.location.href='/'">
                    <box-icon color='dark' name='arrow-back'></box-icon>
                    Return to Home
                </button>
            </div>
        </div>

    </div>
</div>

@endsection
