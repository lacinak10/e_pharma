@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')

<main class="flex-1 p-4 md:p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Toutes les notifications</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $notifications->total() }} notification(s) au total
            </p>
        </div>

        @if(auth()->user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('admin.notifications.markAllAsRead') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition">
                    <i class="fa-solid fa-check-double"></i>
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Liste --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="p-16 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <i class="fa-regular fa-bell text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-600 font-medium text-lg">Aucune notification</p>
                <p class="text-sm text-gray-500 mt-1">Vous êtes à jour !</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    @php
                        $data  = $notification->data;
                        $isNew = is_null($notification->read_at);
                        $colorClasses = match($data['color'] ?? 'gray') {
                            'blue'   => 'bg-blue-100 text-blue-700 ring-blue-200',
                            'indigo' => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
                            'yellow' => 'bg-yellow-100 text-yellow-700 ring-yellow-200',
                            'green'  => 'bg-green-100 text-green-700 ring-green-200',
                            'red'    => 'bg-red-100 text-red-700 ring-red-200',
                            default  => 'bg-gray-100 text-gray-700 ring-gray-200',
                        };
                    @endphp
                    <li class="flex items-start gap-4 p-5 {{ $isNew ? 'bg-blue-50' : 'hover:bg-gray-50' }} transition">
                        {{-- Icône --}}
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $colorClasses }} flex items-center justify-center ring-4 ring-white">
                            <i class="fa-solid {{ $data['icon'] ?? 'fa-bell' }} text-base"></i>
                        </div>

                        {{-- Contenu --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        {{ $data['title'] ?? 'Notification' }}
                                        @if($isNew)
                                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $data['message'] ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        <i class="fa-regular fa-clock mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if(!$isNew)
                                            · <span class="text-green-600">Lu {{ $notification->read_at->diffForHumans() }}</span>
                                        @endif
                                    </p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if(!empty($data['url']))
                                        <a href="{{ $data['url'] }}"
                                           class="text-xs text-blue-600 hover:underline font-medium">
                                            Voir →
                                        </a>
                                    @endif
                                    @if($isNew)
                                        <form method="POST" action="{{ route('admin.notifications.markAsRead', $notification->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded-lg hover:bg-gray-100 transition"
                                                    title="Marquer comme lu">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

</main>
@endsection
