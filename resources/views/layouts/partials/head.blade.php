<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#12211B">

<title>@yield('title', 'ePharma')</title>
<meta name="description" content="@yield('description', 'ePharma — la disponibilité vérifiée avant le déplacement. Commandez vos médicaments à Abidjan et suivez la livraison en direct.')">
<link rel="icon" type="image/jpeg" href="{{ asset('assets/images/logo.jpeg') }}">

{{-- Les trois rôles typographiques du système de design --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Public+Sans:ital,wght@0,300..800;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/epharma-ds.css') }}?v={{ config('app.asset_version', '2') }}">

<script defer src="{{ asset('assets/js/epharma.js') }}?v={{ config('app.asset_version', '2') }}"></script>
