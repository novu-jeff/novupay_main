<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ isset($payload['status']) ? 'Payment ' . ucwords($payload['status']) : 'Payment Expired' }} 
        | NovuPay
    </title>

    @vite(['resources/sass/status.scss', 'resources/js/status.js'])
    <style>
        .outer-wrapper {
            min-height: 140vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            /* background: #f5f6fa; */
        }

        .inner-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .wrapper {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }

        .top {
            text-align: center;
        }

        .icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px auto;
        }

        .icon.success {
            background: #28a745;
        }

        .icon.error {
            background: #dc3545;
        }

        .header h5 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .header p {
            margin: 5px 0 10px 0;
            color: #666;
        }

        .details .items {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 15px;
        }

        .bottom button {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
        }

        .bottom button:first-child {
            background: #2c7be5;
            color: white;
        }

        .bottom button:last-child {
            background: #f1f1f1;
            color: #333;
        }

        @media (max-width: 480px) {
            .wrapper {
                padding: 15px;
            }

            .header h3 {
                font-size: 22px;
            }

            .icon {
                width: 60px;
                height: 60px;
            }
        }
    </style>

</head>
<body>
    @yield('base')
</body>
@yield('scripts')
</html>
