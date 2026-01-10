@extends('layouts.admin')

@section('title', 'E-PHARMA - Admin')
@section('page_title', 'Tableau de bord')

@section('content')
@php
    $role = auth()->user()->role ?? null;
    $isManager = $role === 'manager';
    $isCourier = $role === 'courier';

    /**
     * KPI Manager (sans "Statut")
     * -> Bonnes pratiques : volume, CA, stock, ruptures
     */
    $statsManager = [
        ['label'=>"Commandes aujourd'hui", 'value'=>24, 'icon'=>'fa-solid fa-cart-shopping', 'valueClass'=>'text-primary', 'iconWrapClass'=>'bg-blue-100 text-primary'],
        ['label'=>"CA du mois", 'value'=>"1 280 000 FCFA", 'icon'=>'fa-solid fa-coins', 'valueClass'=>'text-secondary', 'iconWrapClass'=>'bg-indigo-100 text-secondary'],
        ['label'=>"Produits en stock", 'value'=>156, 'icon'=>'fa-solid fa-pills', 'valueClass'=>'text-accent', 'iconWrapClass'=>'bg-green-100 text-accent'],
        ['label'=>"Ruptures", 'value'=>8, 'icon'=>'fa-solid fa-triangle-exclamation', 'valueClass'=>'text-danger', 'iconWrapClass'=>'bg-red-100 text-danger'],
    ];

    /**
     * KPI Livreur
     * -> performance, volume, fiabilité
     */
    $statsCourier = [
        ['label'=>"Assignées", 'value'=>6, 'icon'=>'fa-solid fa-inbox', 'valueClass'=>'text-primary', 'iconWrapClass'=>'bg-blue-100 text-primary'],
        ['label'=>"En cours", 'value'=>2, 'icon'=>'fa-solid fa-truck', 'valueClass'=>'text-secondary', 'iconWrapClass'=>'bg-indigo-100 text-secondary'],
        ['label'=>"Livrées (semaine)", 'value'=>12, 'icon'=>'fa-solid fa-circle-check', 'valueClass'=>'text-accent', 'iconWrapClass'=>'bg-green-100 text-accent'],
        ['label'=>"Taux d’acceptation", 'value'=>"92%", 'icon'=>'fa-solid fa-thumbs-up', 'valueClass'=>'text-gray-800', 'iconWrapClass'=>'bg-gray-100 text-gray-700'],
    ];

    /**
     * Table demo
     */
    $recentOrders = [
        ['id'=>'#EP-1001','customer'=>'Jean Dupont','date'=>'10/05/2023','amount'=>'25 000 FCFA','status'=>'Livré','badge'=>'green'],
        ['id'=>'#EP-1002','customer'=>'Marie Koné','date'=>'10/05/2023','amount'=>'18 500 FCFA','status'=>'En cours','badge'=>'yellow'],
        ['id'=>'#EP-1003','customer'=>'Paul Yao','date'=>'09/05/2023','amount'=>'32 000 FCFA','status'=>'Préparation','badge'=>'blue'],
    ];

    /**
     * Données charts (statiques pour l’instant)
     */
    $labels7Days = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];

    // Manager
    $orders7Days = [12, 18, 9, 22, 15, 28, 24];
    $revenue7Days = [120000, 190000, 90000, 250000, 160000, 320000, 280000];

    $statusBreakdownManager = [
        'labels' => ['En attente', 'Affectée', 'En livraison', 'Livrée', 'Annulée'],
        'values' => [8, 6, 5, 18, 2],
    ];

    $topMedicines = [
        'labels' => ['Doliprane', 'Amoxicilline', 'Vit C', 'Ibuprofène', 'Sirop Toux'],
        'values' => [44, 28, 20, 16, 12],
    ];

    // Courier
    $deliveries7Days = [1, 2, 0, 3, 2, 3, 1];
    $statusBreakdownCourier = [
        'labels' => ['Assignée', 'Acceptée', 'En livraison', 'Livrée', 'Refusée'],
        'values' => [4, 3, 2, 12, 1],
    ];

    // Cards insight (progress)
    $deliveryGoal = 20;
    $deliveredThisWeek = 12;
    $progressPct = min(100, (int) round(($deliveredThisWeek / max(1,$deliveryGoal)) * 100));
@endphp

{{-- KPI --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($isCourier ? $statsCourier : $statsManager as $s)
        <x-admin.stat-card
            :label="$s['label']"
            :value="$s['value']"
            :icon="$s['icon']"
            :valueClass="$s['valueClass']"
            :iconWrapClass="$s['iconWrapClass']"
        />
    @endforeach
</div>

{{-- Insights + Graphiques --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    {{-- Chart 1: Line (Manager: commandes+CA) / (Courier: livraisons) --}}
    <x-admin.card :title="$isCourier ? 'Mes livraisons (7 jours)' : 'Commandes & CA (7 jours)'" class="p-0">
        <div class="p-4">
            <div class="text-sm text-gray-500 mb-3">
                {{ $isCourier ? 'Suivi de ton activité quotidienne' : 'Suivi de la demande et du chiffre d’affaires' }}
            </div>
            <div class="h-64">
                <canvas id="{{ $isCourier ? 'courierLineChart' : 'managerLineChart' }}"></canvas>
            </div>
        </div>
    </x-admin.card>

    {{-- Chart 2: Doughnut status --}}
    <x-admin.card :title="$isCourier ? 'Répartition de mes statuts' : 'Répartition des statuts commandes'" class="p-0">
        <div class="p-4">
            <div class="text-sm text-gray-500 mb-3">
                {{ $isCourier ? 'Où en sont tes commandes assignées' : 'Vue rapide sur le pipeline des commandes' }}
            </div>
            <div class="h-64">
                <canvas id="{{ $isCourier ? 'courierStatusChart' : 'managerStatusChart' }}"></canvas>
            </div>
        </div>
    </x-admin.card>

    {{-- Card insight / progress --}}
    <x-admin.card :title="$isCourier ? 'Objectif hebdomadaire' : 'Qualité & alertes'" class="p-0">
        <div class="p-4 space-y-4">
            @if($isCourier)
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Livraisons cette semaine</div>
                        <div class="text-2xl font-bold text-gray-800">{{ $deliveredThisWeek }}/{{ $deliveryGoal }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-green-100 text-green-700">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-2">
                        <span>Progression</span>
                        <span class="font-semibold text-gray-700">{{ $progressPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-accent h-2 rounded-full" style="width: {{ $progressPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Astuce: accepte rapidement pour améliorer ton taux d’assignation.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Temps moyen</div>
                        <div class="text-lg font-bold text-gray-800">38 min</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Refus</div>
                        <div class="text-lg font-bold text-danger">1</div>
                    </div>
                </div>
            @else
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Stock faible (seuil)</div>
                        <div class="text-2xl font-bold text-gray-800">14 produits</div>
                    </div>
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-700">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Taux de livraison réussie (30j)</div>
                    <div class="flex items-center gap-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-accent h-2 rounded-full" style="width: 93%"></div>
                        </div>
                        <div class="text-sm font-bold text-gray-800">93%</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Panier moyen</div>
                        <div class="text-lg font-bold text-gray-800">7 800 FCFA</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Nouveaux clients</div>
                        <div class="text-lg font-bold text-primary">+18</div>
                    </div>
                </div>
            @endif
        </div>
    </x-admin.card>
</div>

{{-- Graph 3: Top produits (Manager) / Activité (Courier) --}}
<x-admin.card :title="$isCourier ? 'Mon activité (7 jours)' : 'Top médicaments (ventes)'" class="p-0 mb-6">
    <div class="p-4">
        <div class="text-sm text-gray-500 mb-3">
            {{ $isCourier ? 'Volume de livraisons par jour' : 'Les produits les plus demandés' }}
        </div>
        <div class="h-72">
            <canvas id="{{ $isCourier ? 'courierBarChart' : 'managerBarChart' }}"></canvas>
        </div>
    </div>
</x-admin.card>

{{-- Table récente (adaptée au rôle) --}}
<x-admin.card :title="$isCourier ? 'Mes livraisons récentes' : 'Commandes récentes'" class="p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $isCourier ? 'Client + Adresse' : 'Client' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($recentOrders as $o)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $o['id'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['customer'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['date'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['amount'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-admin.badge :text="$o['status']" :variant="$o['badge']" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="#" class="text-primary hover:text-secondary" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection

@push('scripts')
    {{-- Chart.js CDN (pas besoin de Vite) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isCourier = @json($isCourier);

            const labels7 = @json($labels7Days);

            // ===== Manager charts =====
            const managerOrders = @json($orders7Days);
            const managerRevenue = @json($revenue7Days);

            const managerStatus = @json($statusBreakdownManager);
            const topMeds = @json($topMedicines);

            // ===== Courier charts =====
            const courierDeliveries = @json($deliveries7Days);
            const courierStatus = @json($statusBreakdownCourier);

            // Helpers
            const moneyFmt = (v) => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA';

            // ===== LINE =====
            if (!isCourier) {
                const ctx = document.getElementById('managerLineChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels7,
                            datasets: [
                                { label: 'Commandes', data: managerOrders, tension: 0.35 },
                                { label: 'Chiffre d’affaires', data: managerRevenue, tension: 0.35, yAxisID: 'y1' },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ctx.dataset.label === 'Chiffre d’affaires'
                                            ? `${ctx.dataset.label}: ${moneyFmt(ctx.parsed.y)}`
                                            : `${ctx.dataset.label}: ${ctx.parsed.y}`
                                    }
                                }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false },
                                      ticks: { callback: (v) => (v >= 1000 ? (v/1000)+'k' : v) } }
                            }
                        }
                    });
                }
            } else {
                const ctx = document.getElementById('courierLineChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels7,
                            datasets: [
                                { label: 'Livraisons', data: courierDeliveries, tension: 0.35 },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                }
            }

            // ===== DOUGHNUT STATUS =====
            const statusCanvasId = isCourier ? 'courierStatusChart' : 'managerStatusChart';
            const statusData = isCourier ? courierStatus : managerStatus;

            const statusCtx = document.getElementById(statusCanvasId);
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{ data: statusData.values }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        cutout: '60%'
                    }
                });
            }

            // ===== BAR =====
            if (!isCourier) {
                const ctx = document.getElementById('managerBarChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: topMeds.labels,
                            datasets: [{ label: 'Quantité', data: topMeds.values }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                }
            } else {
                const ctx = document.getElementById('courierBarChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels7,
                            datasets: [{ label: 'Livraisons', data: courierDeliveries }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                }
            }
        });
    </script>
@endpush
