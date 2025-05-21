@extends('layouts.status')

@section('base')

<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">
            <div class="top">
                <div class="icon {{strtolower($payload['status']) == 'paid' ? 'success' : 'error'}}">
                    @if(strtolower($payload['status']) == 'paid')
                        <box-icon color='white' size='md' name='check'></box-icon>
                    @else
                        <box-icon color='white' name='x'></box-icon>
                    @endif
                </div>
                <div class="header">
                    <h5>{{$payload['title']}}</h5>
                    <p>{{$payload['message']}}</p>
                    <h3>PHP{{$payload['amount']}}</h3>
                </div>
            </div>
            <hr>
            <div class="mid">
                <h6>Payment Details :</h6>
                <div class="details">
                    <div class="items">
                        <div>
                            Reference No:
                        </div>
                        <div>
                            {{$payload['reference_no']}}
                        </div>
                    </div>
                    <div class="items">
                        <div>
                            Payment Status:
                        </div>
                        <div>
                            {{ucwords($payload['status'])}}
                        </div>
                    </div>
                    <div class="items">
                        <div>
                            Date:
                        </div>
                        <div>
                            {{ucwords($payload['date_paid'])}}
                        </div>
                    </div>
                    <hr>
                    <div class="items">
                        <div>
                            Total Payment
                        </div>
                        <div>
                            PHP{{$payload['amount']}}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center mt-3">
                    <div class="fw-bold">
                        Payment ID
                    </div>
                    <div class="fw-bold text-muted mt-1">
                        {{$payload['payment_id']}}
                    </div>
                </div>
            </div>
        </div>
        <div class="wrapper">
            <div class="others">
                <div class="d-flex justify-content-between m-auto align-items-center gap-3">
                    <div class="d-md-flex align-items-center gap-3">
                        <div class="icon d-none d-md-flex">
                            <box-icon color='white' animation='tada' name='question-mark' ></box-icon>
                        </div>
                        <div class="d-block">
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Trouble with your payment?
                            </div>
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
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
        @if(strtolower($payload['status']) == 'succeed')
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
        @endif
    </div>
</div>

@endsection