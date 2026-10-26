@extends('layouts.status')

@section('base')

<style>
    .outer-wrapper {
        min-height: 100vh;
        align-items: flex-start;
        padding-top: 24px;
    }

    .icon.maintenance {
        background: #0560aa;
    }

    .resume-banner {
        background: #f4f8fc;
        border: 1px solid #d6e6f5;
        border-radius: 10px;
        padding: 14px 12px;
        margin-top: 12px;
    }

    .resume-banner .label {
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7c8f;
        font-weight: 700;
    }

    .resume-banner .when {
        margin-top: 4px;
        font-size: 18px;
        font-weight: 700;
        color: #0560aa;
        line-height: 1.35;
    }
</style>

<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">

            <div class="top">
                <div class="icon maintenance">
                    <box-icon color='white' name='wrench'></box-icon>
                </div>

                <div class="header">
                    <h5>Online Payment Maintenance</h5>

                    <p>
                        We apologize for the inconvenience. Online payment for your
                        Sta. Rita Water District bill is temporarily unavailable while
                        we complete scheduled maintenance.
                    </p>

                    <div class="resume-banner">
                        <div class="label">Online payment returns</div>
                        <div class="when">Monday, October 5, 2026<br>at 5:00 AM</div>
                    </div>

                    <p style="margin-top: 14px;">
                        Your bill details from this QR code have been saved.
                        Thank you for your patience. When payment is back, you can
                        expect a smoother checkout and better service.
                    </p>
                </div>
            </div>

            <hr>

            <div class="mid">
                <h6>Bill details</h6>
                <div class="details">
                    <div class="items">
                        <div>Reference No:</div>
                        <div>{{ $reference_no ?? '-' }}</div>
                    </div>

                    @if(!empty($account_no))
                        <div class="items">
                            <div>Account No:</div>
                            <div>{{ $account_no }}</div>
                        </div>
                    @endif

                    @if(!empty($payor))
                        <div class="items">
                            <div>Account Name:</div>
                            <div>{{ $payor }}</div>
                        </div>
                    @endif

                    @if($amount !== null && $amount !== '')
                        <div class="items">
                            <div>Amount:</div>
                            <div>₱{{ number_format((float) $amount, 2) }}</div>
                        </div>
                    @endif
                </div>

                <hr>

                <div class="text-center mt-3">
                    <div class="fw-bold text-muted" style="font-size: 15px;">
                        If you need to settle this bill before Monday, please pay at the
                        Sta. Rita Water District office.
                    </div>
                </div>
            </div>
        </div>

        <div class="wrapper">
            <div class="others">
                <div class="d-flex justify-content-between m-auto align-items-center gap-3">
                    <div class="d-md-flex align-items-center gap-3">
                        <div class="icon d-none d-md-flex" style="background:#0560aa;">
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
