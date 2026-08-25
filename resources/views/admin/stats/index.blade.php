@extends('layouts.admin')

@section('title', 'Statistiques — ePharma')
@section('page_title', 'Statistiques')
@section('page_subtitle', 'Quatorze jours d’activité')

@section('content')
    <div class="ep-grid ep-grid--stats">
        <x-ep.stat label="Commandes (14 j)" :value="array_sum($orders)" color="#0E5C43" />
        <x-ep.stat label="Livrées (14 j)" :value="array_sum($delivered)" color="#33557F" />
        <x-ep.stat label="Chiffre d'affaires (14 j)"
                   :value="number_format(array_sum($revenue), 0, ',', ' ') . ' F'" color="#B87514" />
        <x-ep.stat label="Note moyenne livreurs"
                   :value="$rating > 0 ? number_format($rating, 1, ',', ' ') : '—'" color="#0E5C43" />
    </div>

    <div class="ep-split">
        <x-ep.card title="Commandes et livraisons" meta="14 derniers jours">
            <div style="height:280px"><canvas id="epTrend" aria-label="Évolution des commandes et livraisons"></canvas></div>
        </x-ep.card>

        <x-ep.card title="Répartition par statut" flush>
            <div style="padding:.5rem 0">
                @foreach($funnel as $label => $total)
                    <div class="ep-row ep-row--nowrap" style="gap:.75rem;padding:.5rem 1.125rem">
                        <span style="font-size:.8125rem;flex:1;min-width:0">{{ $label }}</span>
                        <span class="ep-mono ep-small">{{ $total }}</span>
                    </div>
                @endforeach
            </div>
        </x-ep.card>
    </div>

    <x-ep.card title="Médicaments les plus livrés" flush>
        <div class="ep-table-wrap">
            <table class="ep-table ep-table--cards">
                <thead><tr><th scope="col">Médicament</th><th scope="col">Quantité livrée</th></tr></thead>
                <tbody>
                    @forelse($top as $row)
                        <tr>
                            <td data-label="Médicament">{{ $row->medicine_name }}</td>
                            <td data-label="Quantité"><span class="ep-cell-num">{{ $row->qty }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" data-label=""><span class="ep-small">Aucune livraison enregistrée.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ep.card>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('epTrend');
            if (!canvas || typeof Chart === 'undefined') return;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [
                        { label: 'Commandes', data: @json($orders), tension: .35, borderColor: '#0E5C43', backgroundColor: 'rgba(14,92,67,.08)', fill: true },
                        { label: 'Livrées', data: @json($delivered), tension: .35, borderColor: '#33557F', backgroundColor: 'rgba(51,85,127,.08)', fill: true },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        });
    </script>
@endpush
