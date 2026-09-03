@extends('layouts.artisan')

@section('title', $client->nom.' - Client - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.clients.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Clients</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $client->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-2xl font-bold text-primary dark:text-primary">
                {{ $client->initial }}
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $client->nom }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    {{ $client->type->icon() }} {{ $client->type->label() }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('artisan.clients.destroy', $client) }}" onsubmit="return confirm('Supprimer ce client ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-full text-sm font-medium border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                    Supprimer
                </button>
            </form>
            <a href="{{ route('artisan.clients.index') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                �?� Retour
            </a>
        </div>
    </div>

    <!-- Grille d'information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Informations</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Type</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $client->type->icon() }} {{ $client->type->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Téléphone</span>
                    <a href="tel:{{ $client->telephone }}" class="font-medium text-primary hover:text-primary transition-colors">{{ $client->telephone }}</a>
                </div>
                @if ($client->email)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Email</span>
                        <a href="mailto:{{ $client->email }}" class="font-medium text-primary hover:text-primary transition-colors truncate max-w-48">{{ $client->email }}</a>
                    </div>
                @endif
                @if ($client->adresse)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Adresse</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right max-w-48">{{ $client->adresse }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Créé le</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $client->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if ($client->notes)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">�Y"� Notes</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $client->notes }}</p>
        </div>
        @endif

        <!-- Chantiers du client -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 md:col-span-2">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">�Y"< Chantiers associés</h3>
            @php $clientChantiers = $client->chantiers()->latest()->get(); @endphp
            @if ($clientChantiers->count() > 0)
                <div class="space-y-2">
                    @foreach ($clientChantiers as $chantier)
                        <a href="{{ route('artisan.chantiers.show', $chantier) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $chantier->nom }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $types[$chantier->type] ?? $chantier->type }}
                                    · <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $chantier->statut->colorClass() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $chantier->statut->dotColorClass() }}"></span>
                                        {{ $chantier->statut->label() }}
                                    </span>
                                </div>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($chantier->budget ?? 0, 0, ',', ' ') }} �,�</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun chantier associé à ce client.</p>
            @endif
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="tel:{{ $client->telephone }}" class="bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-full text-sm font-medium transition shadow-md hover:shadow-lg">
            �Y"z Appeler
        </a>
        @if ($client->email)
            <a href="mailto:{{ $client->email }}" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2.5 rounded-full text-sm font-medium transition shadow-md hover:shadow-lg">
                �Y"� Envoyer un email
            </a>
        @endif
        <a href="{{ route('artisan.chantiers.index', ['client_id' => $client->id]) }}" class="bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-full text-sm font-medium transition shadow-md hover:shadow-lg">
            �Y"< Voir ses chantiers
        </a>
    </div>
</div>
@endsection