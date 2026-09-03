@extends('layouts.artisan')

@section('title', 'Nouveau document')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="folder" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau document</span>
    </nav>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg">
            <form action="{{ route('artisan.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Nom du document *</label>
                        <input type="text" name="nom" required value="{{ old('nom') }}"
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background transition-all duration-200 focus:shadow-md @error('nom') border-red-500 @enderror">
                        @error('nom')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Client *</label>
                        <select name="client_id" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background transition-all duration-200 focus:shadow-md @error('client_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Type de document *</label>
                        <select name="type" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background transition-all duration-200 focus:shadow-md @error('type') border-red-500 @enderror">
                            <option value="">Sélectionnez un type</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Chantier (optionnel)</label>
                        <select name="chantier_id" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background transition-all duration-200 focus:shadow-md @error('chantier_id') border-red-500 @enderror">
                            <option value="">Aucun chantier</option>
                            @foreach($chantiers as $chantier)
                                <option value="{{ $chantier->id }}" {{ old('chantier_id') == $chantier->id ? 'selected' : '' }}>
                                    {{ $chantier->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('chantier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Fichier *</label>
                        <input type="file" name="fichier" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background transition-all duration-200 focus:shadow-md @error('fichier') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-muted-foreground">Formats acceptés : PDF, JPG, PNG, DOC, DOCX (max 10MB)</p>
                        @error('fichier')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            Créer
                        </button>
                        <a href="{{ route('artisan.documents.index') }}"
                           class="px-6 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection