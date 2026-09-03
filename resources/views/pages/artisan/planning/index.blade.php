@extends('layouts.artisan')

@section('title', 'Planning - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="calendar" class="w-4 h-4"></i>
        <span>Planning</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="planningApp()">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mon <span class="text-primary">planning</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Gérez vos interventions et rendez-vous</p>
        </div>
        <a href="{{ route('artisan.planning.create') }}" class="bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition shadow-md hover:shadow-lg">
            + Nouvel événement
        </a>
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un événement�?�'])
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 items-center mb-6">
        <a href="{{ route('artisan.planning.index') }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                {{ !request('type') && !request('chantier_id') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
            <i data-lucide="chart-no-axes-column" class="w-4 h-4 inline-block align-middle mr-1"></i> Tous
        </a>
        @foreach ($types as $type)
            <a href="{{ route('artisan.planning.index', ['type' => $type->value]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ request('type') === $type->value ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
                {{ $type->icon() }} {{ $type->label() }}
            </a>
        @endforeach
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $evenements->count() }} événement(s)</span>
    </div>

    <!-- Calendrier grille CSS -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <!-- En-tête du calendrier -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
            @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $jour)
                <div class="px-2 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                    {{ $jour }}
                </div>
            @endforeach
        </div>

        <!-- Grille des jours -->
        <div class="grid grid-cols-7 auto-rows-fr">
            @php
                $startOfWeek = now()->startOfWeek()->subDays(1); // Lundi
                $days = collect();
                for ($i = 0; $i < 35; $i++) {
                    $days->push($startOfWeek->copy()->addDays($i));
                }
            @endphp

            @foreach ($days as $day)
                @php
                    $isCurrentMonth = $day->month === now()->month;
                    $isToday = $day->isToday();
                    $dayEvents = $evenements->filter(function ($event) use ($day) {
                        return $event->date_debut->isSameDay($day);
                    });
                @endphp
                <div class="min-h-[100px] border-r border-b border-gray-200 dark:border-gray-700 last:border-r-0 p-2 transition hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ !$isCurrentMonth ? 'bg-gray-50/50 dark:bg-gray-900/30' : 'bg-white dark:bg-gray-800' }} {{ $dayEvents->count() > 0 ? 'ring-1 ring-inset ring-primary/20 dark:ring-primary/30' : '' }}">
                    <div class="flex items-center justify-center w-7 h-7 rounded-full text-sm font-medium mb-2 {{ $isToday ? 'bg-primary text-white' : ($isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600') }}">
                        {{ $day->day }}
                    </div>

                    <!-- Marqueur visuel pour les dates avec événements -->
                    @if ($dayEvents->count() > 0)
                        <div class="flex justify-center -mt-1.5 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse" title="{{ $dayEvents->count() }} événement(s) ce jour"></span>
                        </div>
                    @endif

                    <div class="space-y-1">
                        @foreach ($dayEvents as $event)
                            <div class="group relative">
                                <a href="{{ route('artisan.planning.show', $event) }}" 
                                    class="block px-2 py-1 rounded text-xs font-medium {{ $event->type->colorClass() }} hover:opacity-80 transition truncate"
                                    title="{{ $event->titre }} ({{ $event->date_debut->format('H:i') }} - {{ $event->date_fin->format('H:i') }})">
                                    {{ $event->type->icon() }} {{ $event->titre }}
                                </a>
                                <div class="hidden group-hover:flex absolute z-10 left-0 top-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-2 gap-1">
                                    <a href="{{ route('artisan.planning.edit', $event) }}" class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 transition"><i data-lucide="pencil" class="w-4 h-4 inline-block align-middle mr-1"></i></a>
                                    <form method="POST" action="{{ route('artisan.planning.destroy', $event) }}" class="inline" onsubmit="return confirm('Supprimer cet événement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition"><i data-lucide="trash-2" class="w-4 h-4 inline-block align-middle mr-1"></i></button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Liste des événements -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="list" class="w-5 h-5 text-primary"></i>
                Mes événements
            </h2>
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $evenements->count() }} événement(s)</span>
        </div>

        @if ($evenements->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-8 text-center">
                <i data-lucide="calendar-off" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Aucun événement pour le moment</p>
                <a href="{{ route('artisan.planning.create') }}" class="inline-block mt-4 bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-full font-semibold text-sm transition shadow-md hover:shadow-lg">
                    + Créer un événement
                </a>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($evenements as $event)
                        <div class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                            <!-- Date badge -->
                            <div class="shrink-0 w-14 h-14 rounded-lg {{ $event->type->colorClass() }} flex flex-col items-center justify-center border border-gray-200 dark:border-gray-600">
                                <span class="text-xs font-medium leading-none">{{ $event->date_debut->format('M') }}</span>
                                <span class="text-lg font-bold leading-none mt-0.5">{{ $event->date_debut->day }}</span>
                            </div>

                            <!-- Event info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $event->titre }}</span>
                                    <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-full {{ $event->type->colorClass() }}">
                                        {{ $event->type->icon() }} {{ $event->type->label() }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        {{ $event->date_debut->format('d/m/Y H:i') }} - {{ $event->date_fin->format('H:i') }}
                                    </span>
                                    @if ($event->chantier)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="hard-hat" class="w-3.5 h-3.5"></i>
                                            {{ $event->chantier->nom }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="shrink-0 flex items-center gap-1.5">
                                <a href="{{ route('artisan.planning.show', $event) }}"
                                    class="p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition"
                                    title="Voir">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('artisan.planning.edit', $event) }}"
                                    class="p-2 rounded-lg text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition"
                                    title="Modifier">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('artisan.planning.destroy', $event) }}" class="inline" onsubmit="return confirm('Supprimer cet événement ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                        title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Nouvel �?vénement -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><i data-lucide="calendar-days" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouvel événement</h2>
                <button @click="modalOpen = false" class="text-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>

            <form method="POST" action="{{ route('artisan.planning.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Titre *</label>
                        <input type="text" name="titre" value="{{ old('titre') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('titre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Chantier associé</label>
                        <select name="chantier_id"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Aucun chantier</option>
                            @foreach ($chantiers as $chantier)
                                <option value="{{ $chantier->id }}" {{ old('chantier_id') == $chantier->id ? 'selected' : '' }}>
                                    {{ $chantier->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('chantier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date et heure de début *</label>
                            <input type="datetime-local" name="date_debut" value="{{ old('date_debut') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date et heure de fin *</label>
                            <input type="datetime-local" name="date_fin" value="{{ old('date_fin') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                        <select name="type" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" {{ old('type') === $type->value ? 'selected' : '' }}>
                                    {{ $type->icon() }} {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition">Créer l'événement</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('planningApp', () => ({
            modalOpen: false,

            openModal() {
                this.modalOpen = true;
            }
        }));
    });
</script>
@endpush
@endsection