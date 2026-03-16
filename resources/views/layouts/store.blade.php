<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-PHARMA — Pharmacie en ligne')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        .pharmacy-gradient { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
    </style>

    @stack('head')
            @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-50 text-gray-900">
    <x-store.header />

    <main class="min-h-[70vh]">
        <x-store.flash />
        @yield('content')
    </main>

    <x-store.footer />

    @stack('scripts')
</body>
</html>
