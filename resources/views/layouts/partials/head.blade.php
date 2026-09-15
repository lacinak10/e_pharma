@php
    /* Titre et description servent trois fois : la page, la carte de partage et
       l'aperçu Twitter. On résout les sections une seule fois plutôt que de
       recopier la phrase par défaut à chaque balise. */
    $epTitle       = trim($__env->yieldContent('title', 'ePharma'));
    $epDescription = trim($__env->yieldContent('description', 'ePharma — la disponibilité vérifiée avant le déplacement. Commandez vos médicaments à Abidjan et suivez la livraison en direct.'));
    $epVersion     = config('app.asset_version', '2');
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#12211B">

<title>{{ $epTitle }}</title>
<meta name="description" content="{{ $epDescription }}">

{{-- Icônes : la pastille (le symbole seul) reste lisible à 16 px, là où le
     lockup complet — nom et accroche compris — ne serait qu'une tache. --}}
<link rel="icon" type="image/png" href="{{ asset('assets/images/logo-mark.png') }}?v={{ $epVersion }}">
<link rel="apple-touch-icon" href="{{ asset('assets/images/logo-apple-touch.png') }}?v={{ $epVersion }}">

{{-- Partage social : là, au contraire, le lockup complet porte le nom et la promesse. --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="ePharma">
<meta property="og:locale" content="fr_CI">
<meta property="og:title" content="{{ $epTitle }}">
<meta property="og:description" content="{{ $epDescription }}">
<meta property="og:image" content="{{ asset('assets/images/logo.jpeg') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $epTitle }}">
<meta name="twitter:description" content="{{ $epDescription }}">
<meta name="twitter:image" content="{{ asset('assets/images/logo.jpeg') }}">

{{-- Les trois rôles typographiques du système de design --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Public+Sans:ital,wght@0,300..800;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/epharma-ds.css') }}?v={{ $epVersion }}">

<script defer src="{{ asset('assets/js/epharma.js') }}?v={{ $epVersion }}"></script>
