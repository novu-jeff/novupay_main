@extends('layouts.status')

@section('base')

<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">
            <div class="top">
                <div class="icon">
                    <box-icon color='white' size='md' name='check'></box-icon>
                </div>
                <div class="header">
                    <h5>Payment Success!</h5>
                    <p>Your payment has been successfully done.</p>
                    <h3>PHP 10,000.00</h3>
                </div>
            </div>
            <hr>
            <div class="mid">
                <h6>Payment Details :</h6>
                <div class="details">
                    <div class="items">
                        <div>
                            Reference No
                        </div>
                        <div>
                            00000012345
                        </div>
                    </div>
                    <div class="items">
                        <div>
                            Payment Status
                        </div>
                        <div>
                            Success
                        </div>
                    </div>
                    <div class="items">
                        <div>
                            Date
                        </div>
                        <div>
                            January 10, 2025 | 10:00 Am
                        </div>
                    </div>
                    <hr>
                    <div class="items">
                        <div>
                            Total Payment
                        </div>
                        <div>
                            PHP 10,000.00
                        </div>
                    </div>
                </div>
                <hr>
            </div>
        </div>
        <div class="wrapper">
            <div class="others">
                <div class="d-flex justify-content-between m-auto align-items-center gap-3">
                    <div class="d-md-flex align-items-center gap-3">
                        <div class="icon d-none d-md-block">
                            <box-icon color='white' animation='tada' name='question-mark' ></box-icon>
                        </div>
                        <div class="d-block">
                            <div>
                                Trouble with your payment?
                            </div>
                            <div>
                                Let us know on our help center!
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
                <button>
                    <box-icon color='white' name='download' ></box-icon>
                    Download Receipt
                </button>
                <button>
                    <box-icon color='dark' name='arrow-back'></box-icon>
                    Go Back
                </button>
            </div>
        </div>
    </div>
</div>

@endsection