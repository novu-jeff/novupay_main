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
