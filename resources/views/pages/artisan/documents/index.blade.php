@extends('layouts.artisan')

@section('title', 'Documents')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="folder" class="w-4 h-4"></i>
        <span>Documents</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Documents</h1>
            <p class="text-sm text-muted-foreground mt-1">Cliquez sur un type de document pour le rédiger</p>
        </div>
    </div>

    <!-- Cartes de types de documents -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $metierTypes = ['devis', 'facture', 'attestation', 'compte_rendu'];
            $cardColors = [
                'devis' => ['bg' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800', 'icon' => 'text-green-600 dark:text-green-400', 'badge' => 'bg-green-600 dark:bg-green-700'],
                'facture' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800', 'icon' => 'text-blue-600 dark:text-blue-400', 'badge' => 'bg-blue-600 dark:bg-blue-700'],
                'attestation' => ['bg' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800', 'icon' => 'text-red-600 dark:text-red-400', 'badge' => 'bg-red-600 dark:bg-red-700'],
                'compte_rendu' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800', 'icon' => 'text-orange-600 dark:text-orange-400', 'badge' => 'bg-orange-600 dark:bg-orange-700'],
            ];
        @endphp

        @foreach($metierTypes as $typeKey)
            @php $c = $cardColors[$typeKey] ?? $cardColors['devis']; @endphp
            <div class="relative rounded-xl border shadow-sm transition-all duration-200 {{ $c['bg'] }} p-5">
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="p-3 rounded-full bg-white/80 dark:bg-gray-700/80 shadow-sm">
                        <i data-lucide="{{ \App\Models\Document::ICONS[$typeKey] ?? 'file' }}" class="w-7 h-7 {{ $c['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">{{ $types[$typeKey] ?? ucfirst($typeKey) }}</h3>
                        <p class="text-xs text-muted-foreground mt-1">Utilisez le bouton ci-dessous pour créer</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white {{ $c['badge'] }}">
                        {{ $documentCounts[$typeKey] ?? 0 }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Boutons de création par type de document -->
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $typeColors = [
                'devis' => 'bg-green-500 hover:bg-green-600',
                'facture' => 'bg-blue-500 hover:bg-blue-600',
                'attestation' => 'bg-red-500 hover:bg-red-600',
                'compte_rendu' => 'bg-orange-500 hover:bg-orange-600',
            ];
            $createLabels = [
                'devis' => 'Nouveau devis',
                'facture' => 'Nouvelle facture',
                'attestation' => 'Nouvelle attestation',
                'compte_rendu' => 'Nouveau compte rendu ',
            ];
        @endphp
        @foreach(['devis' => 'Devis', 'facture' => 'Facture', 'attestation' => 'Attestation', 'compte_rendu' => 'Compte rendu'] as $typeKey => $typeLabel)
            <a href="{{ route('artisan.documents.create', ['type' => $typeKey]) }}"
               class="{{ $typeColors[$typeKey] ?? 'bg-gray-500 hover:bg-gray-600' }} text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <i data-lucide="{{ \App\Models\Document::ICONS[$typeKey] }}" class="w-4 h-4"></i>
                {{ $createLabels[$typeKey] ?? 'Nouvelle '.$typeLabel }}
            </a>
        @endforeach
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un document…'])
    </div>

    <!-- Filtres -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg">
        <form method="GET" action="{{ route('artisan.documents.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Client</label>
                <select name="client_id" class="w-full max-w-xs rounded-lg border-border focus:border-orange-500 focus:ring-orange-500 bg-background">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Type</label>
                <select name="type" class="w-full max-w-xs rounded-lg border-border focus:border-orange-500 focus:ring-orange-500 bg-background">
                    <option value="">Tous les types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full max-w-xs px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des documents -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg">
        <x-documents-list :documents="$documents" :show-actions="true" :clients="$clients" />
    </div>

    @if($documents->hasPages())
        <div class="mt-4">
            {{ $documents->links() }}
        </div>
    @endif
</div>

@endsection
