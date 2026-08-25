@props([
    'title'    => 'Tableau de bord',
    'subtitle' => null,
])

@php
    $user   = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $now    = now()->locale('fr');
@endphp

<header class="ep-topbar">
    <button type="button" class="ep-burger" data-ep-sidebar-toggle
            aria-expanded="false" aria-controls="ep-sidebar" aria-label="Ouvrir le menu">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <div style="min-width:0">
        <h1 class="ep-topbar__title">{{ $title }}</h1>
        <p class="ep-topbar__meta">
            {{ $subtitle ?: $now->isoFormat('dddd D MMMM YYYY') . ' · ' . $now->format('H:i') . ' · flux en direct' }}
        </p>
    </div>

    <div class="ep-topbar__actions">
        <span class="ep-live">Temps réel actif</span>

        <a href="{{ route('admin.notifications.index') }}" class="ep-btn ep-btn--ghost ep-btn--md"
           aria-label="Notifications{{ $unread ? " ({$unread} non lues)" : '' }}">
            <i class="fa-regular fa-bell" aria-hidden="true"></i>
            @if($unread)
                <span class="ep-mono" style="color:var(--ep-red)">{{ $unread > 99 ? '99+' : $unread }}</span>
            @endif
        </a>

        @isset($actions)
            {{ $actions }}
        @else
            @if($user->isManager())
                <a href="{{ route('manager.orders.index') }}" class="ep-btn ep-btn--ghost ep-btn--md">Exporter</a>
                <a href="{{ route('manager.orders.index', ['status' => \App\Enums\OrderStatus::PENDING_VALIDATION->value]) }}"
                   class="ep-btn ep-btn--primary ep-btn--md">À valider</a>
            @endif
        @endisset
    </div>
</header>
