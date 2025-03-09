<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment {{ucwords($status)}} | NovuPay</title>
    @vite(['resources/sass/status.scss', 'resources/js/status.js'])
</head>
<body>
    @yield('base')
</body>
@yield('scripts')
</html>
