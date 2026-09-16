@php
    $user = auth()->user();
    $menu = app(\App\Support\BackOfficeNavigation::class)->for($user);
    $role = $user->isManager() ? 'Back-office' : 'Espace livreur';
@endphp

<aside class="ep-sidebar" data-ep-sidebar data-open="false" aria-label="Navigation principale">
    <div class="ep-sidebar__brand">
        <img class="ep-sidebar__mark" src="{{ asset('assets/images/logo.jpeg') }}"
             alt="" aria-hidden="true" width="30" height="30">
        <div style="min-width:0">
            <p class="ep-sidebar__name">ePharma</p>
            <p class="ep-sidebar__role">{{ $role }}</p>
        </div>
        <button type="button" class="ep-spacer ep-viewer__btn" data-ep-sidebar-toggle
                aria-label="Fermer le menu" style="display:none">✕</button>
    </div>

    <nav class="ep-nav">
        @foreach($menu as $item)
            <a href="{{ $item['url'] }}" class="ep-nav__link"
               @if($item['active']) aria-current="page" @endif>
                <span class="ep-nav__label">{{ $item['label'] }}</span>
                @if($item['count'])
                    <span class="ep-nav__count @if($item['urgent']) ep-nav__count--urgent @endif">{{ $item['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="ep-sidebar__user">
        <div class="ep-row ep-row--nowrap" style="gap:.625rem">
            <img src="{{ $user->avatar_url }}" alt="" class="ep-sidebar__avatar">
            <div style="min-width:0;flex:1">
                <p style="font-size:.8125rem;color:#fff;font-weight:650;margin:0">{{ $user->short_name }}</p>
                <p class="ep-mono ep-sidebar__user-role" style="color:var(--ep-dark-muted);margin:0">
                    {{ $user->isManager() ? 'Manager' : 'Livreur' }}
                    @if($user->isCourier() && $user->zone) · {{ $user->zone }} @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:.875rem">
            @csrf
            <button type="submit" class="ep-nav__link" style="width:100%;padding-left:0;background:none;border:0;cursor:pointer;text-align:left">
                Déconnexion
            </button>
        </form>
    </div>
</aside>
