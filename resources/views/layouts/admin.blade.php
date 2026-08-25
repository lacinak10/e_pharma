<!DOCTYPE html>
<html lang="fr" class="ep-root">
<head>
    @include('layouts.partials.head')

    @stack('head')
</head>

<body class="ep-root" style="background: var(--ep-bg-warm)">
<a href="#ep-main" class="ep-sr-only">Aller au contenu</a>

<div class="ep-admin">
    <x-admin.sidebar />

    <div class="ep-main" id="ep-main">
        <x-admin.topbar
            :title="trim($__env->yieldContent('page_title', 'Tableau de bord'))"
            :subtitle="trim($__env->yieldContent('page_subtitle'))" />

        @hasSection('alertbar')
            @yield('alertbar')
        @endif

        <main class="ep-main__body">
            <x-ep.flash />
            @yield('content')
        </main>
    </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
