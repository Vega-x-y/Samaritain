@extends('layouts.base')

@section('title', 'Statistiques')

@section('content')
    <div class="space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Tableau de bord des statistiques</h2>

                <!-- Cartes de statistiques principales -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Total Pass</div>
                        <div class="text-3xl font-bold">{{ $statistics['total'] }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Actifs</div>
                        <div class="text-3xl font-bold">{{ $statistics['active'] }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Expirés</div>
                        <div class="text-3xl font-bold">{{ $statistics['expired'] }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Utilisés</div>
                        <div class="text-3xl font-bold">{{ $statistics['used'] }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Suspendus</div>
                        <div class="text-3xl font-bold">{{ $statistics['suspended'] }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-4 text-white">
                        <div class="text-sm opacity-90">Visites totales</div>
                        <div class="text-3xl font-bold">{{ $statistics['total_visits'] }}</div>
                    </div>
                </div>

                <!-- Alertes -->
                @if ($expiringSoon > 0)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>{{ $expiringSoon }} pass(s)</strong> expirent dans les 3 prochains jours.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Pass les plus actifs -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">📊 Pass les plus actifs</h3>
                        <div class="space-y-3">
                            @forelse($mostActivePasses as $pass)
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium">{{ $pass->holder_name }}</div>
                                        <div class="text-sm text-gray-500">{{ substr($pass->uuid, 0, 8) }}...</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-blue-600">{{ $pass->scans_count }}</div>
                                        <div class="text-xs text-gray-500">visites</div>
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr class="border-gray-200">
                                @endif
                            @empty
                                <p class="text-gray-500">Aucune donnée disponible</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Derniers scans -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">🔄 Derniers scans</h3>
                        <div class="space-y-3">
                            @forelse($recentScans as $scan)
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium">{{ $scan->pass->holder_name }}</div>
                                        <div class="text-sm text-gray-500">par {{ $scan->user->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm">{{ $scan->scanned_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-xs text-gray-500">{{ $scan->device_info }}</div>
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr class="border-gray-200">
                                @endif
                            @empty
                                <p class="text-gray-500">Aucun scan récent</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Graphique simplifié -->
                <div class="mt-6 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📈 Distribution des pass</h3>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Actifs</span>
                                <span>{{ $statistics['active'] }}
                                    ({{ $statistics['total'] > 0 ? round(($statistics['active'] / $statistics['total']) * 100) : 0 }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    style="width: {{ $statistics['total'] > 0 ? ($statistics['active'] / $statistics['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Expirés</span>
                                <span>{{ $statistics['expired'] }}
                                    ({{ $statistics['total'] > 0 ? round(($statistics['expired'] / $statistics['total']) * 100) : 0 }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-600 h-2 rounded-full"
                                    style="width: {{ $statistics['total'] > 0 ? ($statistics['expired'] / $statistics['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Utilisés</span>
                                <span>{{ $statistics['used'] }}
                                    ({{ $statistics['total'] > 0 ? round(($statistics['used'] / $statistics['total']) * 100) : 0 }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-600 h-2 rounded-full"
                                    style="width: {{ $statistics['total'] > 0 ? ($statistics['used'] / $statistics['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
