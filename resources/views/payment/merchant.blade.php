@extends('layouts.payment')

@section('base')

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="payment-card text-center p-5">
            <h4 class="mb-3 text-uppercase fw-bold">{{ $payload['merchant'] }}</h4>
            <p class="text-muted">Scan the QR code below to proceed with the payment.</p>
            <div class="qr-code">
                {!! $payload['qr_code'] !!}
            </div>
            <form action="{{route('payment.other.merchants', ['payment_id' => $payload['operation_id'], 'reference_no' => $payload['reference_no']])}}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary mt-5 px-5 py-3 text-uppercase">Choose Other Merchants</button>
            </form>
            <div class="mt-3" style="font-size: 12px;">
                {{$payload['external_id']}}
            </div>
            <div class="mt-5 text-uppercase fw-bold text-muted" style="font-size: 12px;">
                <div>Powered by NovuPay</div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #19577d;
        }
        .payment-card {
            margin: auto;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
            padding: 20px;
        }
    </style>
@endsection

@section('scripts')
    <script type="module">
            $(function() {
                const operationId = @json($payload['operation_id'] ?? '');
                
                if (!operationId) {
                    console.error('Operation ID is missing.');
                    return;
                }

                const url = `{!! route('payment.status', ['operation_id' => '__OPERATION_ID__']) !!}`.replace('__OPERATION_ID__', encodeURIComponent(operationId));

                async function checkPaymentStatus() {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{csrf_token()}}'
                            },
                            body: JSON.stringify({ isApi: true })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.status == 'paid') {
                            handleSuccessfulPayment(data);
                        } else if (data.status != 'processing') {
                            window.location.reload();
                        } else {
                            setTimeout(checkPaymentStatus, 5000);
                        }
                    } catch (error) {
                        console.error('Error checking payment status:', error);
                        setTimeout(checkPaymentStatus, 5000);
                    }
                }

                async function handleSuccessfulPayment(data) {
                    try {
                        const { external: externalCallback, internal: internalCallback } = data.callback;
                        const csrfToken = '{{csrf_token()}}';

                        const requests = [];

                        if (externalCallback) {
                            requests.push(fetch(externalCallback, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    reference_no: data.reference_no,
                                    payment_id: data.payment_id
                                })
                            }).catch(error => console.error('External callback error:', error)));
                        }

                        if (internalCallback) {
                            requests.push(fetch(internalCallback, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    reference_no: data.reference_no,
                                })
                            }).catch(error => console.error('Internal callback error:', error)));
                        }

                        await Promise.all(requests);

                        window.location.reload();

                    } catch (error) {
                        console.error('Error handling payment:', error);
                    }
                }


                checkPaymentStatus();
            });

    </script>
@endsection
