@extends('layouts.status')

@section('base')

@php
    $status = strtolower($payload['status'] ?? 'unknown');

    $isSuccess = in_array($status, ['paid', 'completed', 'succeeded'], true);
    $isPending = in_array($status, ['pending', 'processing'], true);
    $isFailed  = in_array($status, ['failed', 'cancelled', 'canceled', 'declined'], true);
@endphp

<div class="outer-wrapper">
    <div class="inner-wrapper">
        <div class="wrapper">

            {{-- TOP STATUS HEADER --}}
            <div class="top">
                <div class="icon {{ $isSuccess ? 'success' : ($isPending ? 'pending' : 'error') }}">
                    @if($isSuccess)
                        <box-icon color='white' size='md' name='check'></box-icon>
                    @elseif($isPending)
                        <box-icon color='white' name='time'></box-icon>
                    @else
                        <box-icon color='white' name='x'></box-icon>
                    @endif
                </div>

                <div class="header">
                    <h5>
                        {{ $isSuccess
                            ? 'Payment Successful!'
                            : ($isPending
                                ? 'Payment Pending'
                                : ($isFailed ? 'Payment Failed' : ($payload['title'] ?? 'Payment Status'))) }}
                    </h5>

                    <p>
                        {{ $isSuccess
                            ? 'Thank you! Your payment has been successfully processed.'
                            : ($isPending
                                ? 'Your payment is still being processed. Please check back shortly.'
                                : ($isFailed ? 'Unfortunately, your payment was not completed.' : ($payload['message'] ?? ''))) }}
                    </p>

                    {{-- Total on top = Amount Due + Surcharge + Convenience Fee (for Kaelco breakdown) --}}
                    <h3>PHP{{ $payload['amount'] ?? '0.00' }}</h3>
                </div>
            </div>

            <hr>

            {{-- PAYMENT DETAILS --}}
            <div class="mid">
                <h6>Payment Details :</h6>

                <div class="details">
                    <div class="items">
                        <div>Reference No:</div>
                        <div>{{ $payload['reference_no'] ?? '-' }}</div>
                    </div>

                    <div class="items">
                        <div>Payment Status:</div>
                        <div>{{ $isSuccess ? 'Paid' : ucwords($payload['status'] ?? 'Unknown') }}</div>
                    </div>

                    <div class="items">
                        <div>Date:</div>
                        <div>{{ $payload['date_paid'] ?? now()->format('M d, Y h:i A') }}</div>
                    </div>

                    {{-- Only show expiration for UNPAID statuses --}}
                    @if(!$isSuccess && isset($payload['expires_at']))
                        <div class="items">
                            <div>Expires On:</div>
                            <div class="{{ \Carbon\Carbon::parse($payload['expires_at'])->isPast() ? 'text-danger fw-bold' : 'text-warning fw-bold' }}">
                                {{ \Carbon\Carbon::parse($payload['expires_at'])->format('M d, Y h:i A') }}
                                @if(\Carbon\Carbon::parse($payload['expires_at'])->isPast())
                                    (Expired)
                                @else
                                    ({{ \Carbon\Carbon::parse($payload['expires_at'])->diffForHumans() }})
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- BILL MONTHS --}}
                    @if(!empty($payload['bill_month']))
                    <div class="items">
                        <div>Billing Months:</div>
                        <div>
                            @foreach($payload['bill_month'] as $m)
                                <div>{{ $m }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Kaelco: breakdown (Amount, Disconnection Fee, Surcharge) --}}
                    @if(!empty($payload['show_breakdown']))
                    <hr>
                    <div class="items">
                        <div>Amount</div>
                        <div>PHP{{ $payload['amount_due'] ?? '0.00' }}</div>
                    </div>
                    @if(isset($payload['disconnection_fee']) && (float) str_replace(',', '', $payload['disconnection_fee']) > 0)
                    <div class="items">
                        <div>Disconnection Fee</div>
                        <div>PHP{{ $payload['disconnection_fee'] }}</div>
                    </div>
                    @endif
                    @if(isset($payload['surcharge']) && (float) str_replace(',', '', $payload['surcharge']) > 0)
                    <div class="items">
                        <div>Surcharge</div>
                        <div>PHP{{ $payload['surcharge'] }}</div>
                    </div>
                    @endif
                    @endif

                    <hr>

                    <div class="items">
                        <div>Total Payment</div>
                        <div>PHP{{ $payload['amount'] ?? '0.00' }}</div>
                    </div>
                </div>

                <hr>

                <div class="text-center mt-3">
                    <div class="fw-bold">Payment ID</div>
                    <div class="fw-bold text-muted mt-1">{{ $payload['payment_id'] ?? '-' }}</div>
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
                                Trouble with your payment?
                            </div>
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Let us know on our help center!
                            </div>
                            <div class="mb-0 fw-bold text-muted" style="font-size: 14px;">
                                Or send us an email at <a href="mailto:support@novupay.ph">support@novupay.ph</a>
                            </div>
                        </div>
                    </div>
                    <div class="icon">
                        <box-icon name='chevron-right'></box-icon>
                    </div>
                </div>
            </div>
        </div>

        {{-- DOWNLOAD BUTTONS (PAID or PENDING counted as success) --}}
        @php
            $pdfId = $payload['payment_id'] ?? null;
            $canDownloadPdf = $isSuccess
                && !empty($pdfId)
                && $pdfId !== '-'
                && is_numeric($pdfId);
        @endphp
        @if($isSuccess || $isPending)
            <div class="wrapper">
                <div class="bottom">
                    @if($canDownloadPdf)
                        <button onclick="downloadPDF()">
                            <box-icon color='white' name='download'></box-icon>
                            Download Copy
                        </button>
                    @endif

                    <button onclick="window.location.href='/'">
                        <box-icon color='dark' name='arrow-back'></box-icon>
                        Go Back
                    </button>
                </div>
            </div>

            @if($canDownloadPdf)
            {{-- DOWNLOAD FUNCTION --}}
            <script>
                function downloadPDF() {
                    const url = "{{ route('payment.pdf', ['id' => $pdfId]) }}";
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = "";
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }
            </script>
            @endif

        @endif

    </div>
</div>

@endsection
