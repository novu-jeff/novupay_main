@extends('layouts.payment')

@section('base')

    <div class="d-flex justify-content-center align-items-center vh-100">
        <iframe src="{{$payload['redirect_url']}}" frameborder="0"></iframe>
        <div class="overlay">
            <form action="{{route('payment.other.merchants', ['payment_id' => $payload['operation_id'], 'reference_no' => $payload['reference_no']])}}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary fw-bold mt-4 px-5 py-3 text-uppercase">Choose Other Merchants</button>
            </form>
        </div>
    </div>

    <style>
        body {
            background-color: #f6f6f6;
        }
        .payment-card {
            margin: auto;
            border-radius: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
            padding: 20px;
        }
        img {
            height: 200px;
            width: 200px;
        }

        iframe {
            width: 100%;
            height: 100%;
        }

        .overlay {
            position: absolute;
            bottom: 50px;
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
