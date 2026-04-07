<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'E-PHARMA')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('/assets/images/logo.jpeg') }}">

    {{-- CSS global (si tu en as) --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/epharmaclient/css/app.css') }}"> --}}

    {{-- Head spécifique des pages --}}
    @yield('head')
            @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-white">
    @include("components.navbar")

    @yield('content')

    @include("components.footer")

</body>
</html>
