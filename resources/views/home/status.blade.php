@extends('layouts.status')

@section('base')

    <div class="container">
        <div class="outer-wrapper">
            <div class="inner-wrapper">
                <div class="top">
                    <div class="icon">
                        <box-icon name='check'></box-icon>
                    </div>
                    <div class="header">
                        <h5>Payment Success!</h5>
                        <p>Your payment has been successfully done.</p>
                    </div>
                </div>
                <hr>
                <div class="mid">
                    <h5>Payment Details</h5>
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
                                January 10, 2025
                            </div>
                        </div>
                        <div class="items">
                            <div>
                                Total Payment
                            </div>
                            <div>
                                PHP 100.00
                            </div>
                        </div>
                    </div>
                    <div class="others">
                        <div class="d-flex">
                            <div class="icon">

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
                    </div>
                </div>
                <div class="bottom">
                    <button class="btn-download-receipt">
                        <box-icon name='download' ></box-icon>
                        Download Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection