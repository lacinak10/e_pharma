@extends('layouts.admin')

@section('title', $courier->name . ' — ePharma')
@section('page_title', $courier->name)
@section('page_subtitle', $courier->courierState() . ($courier->zone ? ' · ' . $courier->zone : ''))

@section('content')
    <div class="ep-grid ep-grid--stats">
        <x-ep.stat label="Livrées" :value="$stats['delivered']" color="#0E5C43" />
        <x-ep.stat label="En cours" :value="$stats['delivering'] + $stats['accepted']" color="#33557F" />
        <x-ep.stat label="Assignées" :value="$stats['assigned']" color="#B87514" />
        <x-ep.stat label="Refusées" :value="$stats['refused']" color="#A6382F" />
        <x-ep.stat label="Taux d'acceptation" :value="$stats['acceptRate'] . ' %'" color="#0E5C43" />
    </div>

    <div class="ep-split">
        <x-ep.card title="Courses récentes" flush>
            @if($recentAssignments->isEmpty())
                <x-ep.empty title="Aucune course." text="Ce livreur n'a pas encore reçu de course." />
            @else
                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Référence</th>
                                <th scope="col">Client</th>
                                <th scope="col">Étape</th>
                                <th scope="col">Attribuée</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAssignments as $assignment)
                                <tr>
                                    <td data-label="Référence">
                                        <span class="ep-cell-ref">{{ $assignment->order?->reference ?? '—' }}</span>
                                    </td>
                                    <td data-label="Client" class="ep-cell-name">
                                        <span class="ep-small">{{ $assignment->order?->client?->short_name ?? '—' }}</span>
                                    </td>
                                    <td data-label="Étape">
                                        @if($assignment->order)
                                            <x-ep.badge :status="$assignment->order->status" />
                                        @endif
                                    </td>
                                    <td data-label="Attribuée">
                                        <span class="ep-small">
                                            {{ $assignment->assigned_at?->locale('fr')->isoFormat('D MMM · HH:mm') ?? '—' }}
                                        </span>
                                    </td>
                                    <td data-label="Action">
                                        @if($assignment->order)
                                            <a href="{{ route('manager.orders.show', $assignment->order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ep.card>

        <div class="ep-stack">
            <x-ep.card title="Le livreur">
                <x-ep.courier-card :courier="$courier" :actions="false" />

                <div class="ep-stack" style="gap:.5rem;margin-top:1.25rem">
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">E-mail</span>
                        <span class="ep-small">{{ $courier->email }}</span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">Téléphone</span>
                        <a href="tel:{{ preg_replace('/\s+/', '', $courier->phone ?? '') }}" class="ep-mono ep-small">
                            {{ $courier->phone ?? '—' }}
                        </a>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">Compte</span>
                        <x-ep.badge :tone="$courier->is_active ? 'green' : 'neutral'"
                                    :label="$courier->is_active ? 'Actif' : 'Désactivé'" />
                    </div>
                </div>

                <x-slot:footer>
                    <div class="ep-row">
                        <a href="{{ route('manager.couriers.edit', $courier) }}" class="ep-btn ep-btn--ghost ep-btn--md">Modifier</a>
                        <form method="POST" action="{{ route('manager.couriers.destroy', $courier) }}"
                              onsubmit="return confirm('Retirer ce livreur ? Son historique est conservé.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--md">Retirer</button>
                        </form>
                    </div>
                </x-slot:footer>
            </x-ep.card>

            <x-ep.card title="Avis reçus">
                @forelse($courier->reviews()->with('client:id,name')->latest()->limit(4)->get() as $review)
                    <div style="padding:.625rem 0;border-bottom:1px solid var(--ep-rule-soft)">
                        <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                            <span class="ep-stars" style="font-size:.8125rem">{{ $review->stars }}</span>
                            <time class="ep-mono ep-small">{{ $review->created_at->locale('fr')->isoFormat('D MMM') }}</time>
                        </div>
                        @if($review->comment)
                            <p class="ep-small" style="margin:.375rem 0 0">« {{ Str::limit($review->comment, 110) }} »</p>
                        @endif
                    </div>
                @empty
                    <p class="ep-small" style="margin:0">Aucun avis pour l'instant.</p>
                @endforelse
            </x-ep.card>
        </div>
    </div>
@endsection
