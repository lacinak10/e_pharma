@extends('layouts.admin')

@section('title', 'Notifications — ePharma')
@section('page_title', 'Notifications')
@section('page_subtitle', auth()->user()->unreadNotifications()->count() . ' non lue(s)')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Toutes les notifications</h2>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('admin.notifications.markAllAsRead') }}" class="ep-spacer">
                    @csrf
                    <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Tout marquer comme lu</button>
                </form>
            @endif
        </header>

        @if($notifications->isEmpty())
            <x-ep.empty title="Tout est calme."
                        text="Les nouvelles commandes et les changements de statut apparaissent ici." />
        @else
            <div>
                @foreach($notifications as $notification)
                    @php
                        $data   = $notification->data;
                        $unread = $notification->unread();
                        $tone   = match ($data['color'] ?? 'gray') {
                            'green'  => 'green',
                            'yellow' => 'amber',
                            'red'    => 'red',
                            'indigo', 'blue' => 'blue',
                            default  => 'neutral',
                        };
                    @endphp
                    <article class="ep-notif"
                             style="background:{{ $unread ? '#FCFBF8' : 'transparent' }}">
                        <span class="ep-badge ep-badge--{{ $tone }}" style="flex:none">{{ $data['title'] ?? 'Notification' }}</span>

                        <div class="ep-notif__body">
                            <p class="ep-body" style="margin:0">{{ $data['message'] ?? '' }}</p>
                            <p class="ep-mono ep-small" style="margin:.25rem 0 0">
                                {{ $notification->created_at->locale('fr')->isoFormat('D MMM YYYY · HH:mm') }}
                                @unless($unread) · lue @endunless
                            </p>
                        </div>

                        <div class="ep-row ep-notif__actions" style="gap:.375rem">
                            @if($url = ($data['url'] ?? null))
                                <a href="{{ $url }}" class="ep-btn ep-btn--ghost ep-btn--sm">Ouvrir</a>
                            @endif
                            @if($unread)
                                <form method="POST" action="{{ route('admin.notifications.markAsRead', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Marquer lue</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($notifications->hasPages())
            <x-slot:footer>{{ $notifications->links() }}</x-slot:footer>
        @endif
    </x-ep.card>
@endsection
