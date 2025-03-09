<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment | NovuPay</title>
    @yield('styles')
    @vite(['resources/sass/payment.scss', 'resources/sass/status.scss', 'resources/js/app.js'])

</head>
<body>
    @yield('base')
</body>
@yield('scripts')
</html>
