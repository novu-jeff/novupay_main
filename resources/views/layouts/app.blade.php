<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NovuPay</title>
    <link rel="icon" type="image/png" href="{{ asset('images/novupay-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/novupay-icon.png') }}">
    @vite(['resources/sass/app.scss', 'resources/sass/status.scss', 'resources/js/app.js'])
</head>
<body>
    @include('layouts.navbar')
    @yield('base')
    @include('layouts.footer')
    <div class="scroll-top">
        <box-icon color='white' name='up-arrow-alt'></box-icon>
    </div>
</body>
@yield('scripts')
</html>
