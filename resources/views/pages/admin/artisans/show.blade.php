@extends('layouts.dashboard')

@section('title', $artisan->business_name)

@section('content')
    <div>
        <div class="max-w-7xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.artisans.index') }}"
                class="text-primary text-xs font-medium mb-2 inline-block">
                &larr; Retour à la liste
            </a>
            <div class="flex items-center justify-between">
                <h1 class="md:text-2xl text-lg font-bold text-gray-700">{{ $artisan->business_name }}</h1>
                <div class="flex gap-2">
                    @if (!$artisan->verified)
                        <form action="{{ route('admin.artisans.verify', $artisan) }}" method="POST">
                            @csrf
                            <x-btn type="submit" size="sm">
                                <x-slot:prefix>
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                </x-slot:prefix>
                                Valider l'artisan
                            </x-btn>
                        </form>
                    @endif
                    <form action="{{ route('admin.artisans.suspend', $artisan) }}" method="POST">
                        @csrf
                        <x-btn type="submit" style="warning" size="sm">
                            <x-slot:prefix>
                                <i data-lucide="{{ $artisan->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                            </x-slot:prefix>
                            {{ $artisan->is_active ? 'Suspendre' : 'Activer' }}
                        </x-btn>
                    </form>
                    <form action="{{ route('admin.artisans.destroy', $artisan) }}" method="POST"
                        onsubmit="return confirm('Supprimer définitivement cet artisan ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit" style="destructive" size="sm">
                            <x-slot:prefix>
                                <i data-lucide="trash" class="w-4 h-4"></i>
                            </x-slot:prefix>
                            Supprimer
                        </x-btn>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Infos -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Informations</h2>
                    <dl class="grid grid-cols-2 gap-4">
                        <div class="text-sm">
                            <dt class="text-gray-500">Profession</dt>
                            <dd class="font-medium text-gray-700">{{ $artisan->profession }}</dd>
                        </div>
                        <div class="text-sm">
                            <dt class="text-gray-500">Ville</dt>
                            <dd class="font-medium text-gray-700">{{ $artisan->city }}</dd>
                        </div>
                        <div class="text-sm">
                            <dt class="text-gray-500">Téléphone</dt>
                            <dd class="font-medium text-gray-700">{{ $artisan->phone }}</dd>
                        </div>
                        <div class="text-sm">
                            <dt class="text-gray-500">WhatsApp</dt>
                            <dd class="font-medium text-gray-700">{{ $artisan->whatsapp ?? '-' }}</dd>
                        </div>
                        <div class="text-sm">
                            <dt class="text-gray-500">Expérience</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $artisan->experience ? $artisan->experience . ' an(s)' : '-' }}
                            </dd>
                        </div>
                        <div class="text-sm">
                            <dt class="text-gray-500">Statut</dt>
                            <dd>
                                @if ($artisan->verified)
                                    <span class="px-2 py-1 text-xs font-medium text-emerald-600 bg-emerald-100 rounded-full">Vérifié</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-yellow-600 bg-yellow-100 rounded-full">En attente</span>
                                @endif
                                @if (!$artisan->is_active)
                                    <span class="px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full ml-1">Suspendu</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Catégories -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Spécialités</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($artisan->categories as $category)
                            <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">
                                {{ $category->name }}
                            </span>
                        @endforeach
                        @if($artisan->categories->isEmpty())
                            <p class="text-sm text-gray-400">Aucune spécialité renseignée</p>
                        @endif
                    </div>
                </div>

                <!-- Bio -->
                @if ($artisan->bio)
                    <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-3">Description</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $artisan->bio }}</p>
                    </div>
                @endif

                <!-- Réalisations -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">
                        Réalisations <span class="text-gray-400 text-sm">({{ $artisan->projects->count() }})</span>
                    </h2>
                    @if ($artisan->projects->isNotEmpty())
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($artisan->projects as $project)
                                <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    @if ($project->image)
                                        <img src="{{ Storage::url($project->image) }}" alt="{{ $project->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="text-white text-xs font-medium px-2 py-1 bg-black/70 rounded">{{ $project->title }}</span>
                                        </div>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i data-lucide="image" class="w-8 h-8"></i>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i data-lucide="camera" class="w-12 h-12 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400">Aucune réalisation</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Utilisateur -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Propriétaire</h2>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($artisan->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $artisan->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $artisan->user->email }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        Membre depuis {{ $artisan->user->created_at->format('d/m/Y') }}
                    </p>
                </div>

                <!-- Stats rapides -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Statistiques</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $artisan->reviews_count ?? 0 }}</div>
                            <div class="text-xs text-gray-500">Avis</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-500">{{ number_format($artisan->average_rating ?? 0, 1) }}</div>
                            <div class="text-xs text-gray-500">Note moyenne</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-600">{{ $artisan->contacts_count ?? 0 }}</div>
                            <div class="text-xs text-gray-500">Contacts</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $artisan->projects->count() }}</div>
                            <div class="text-xs text-gray-500">Réalisations</div>
                        </div>
                    </div>
                </div>

                <!-- Avis récents -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Derniers avis</h2>
                    @if ($artisan->reviews->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($artisan->reviews->take(5) as $review)
                                <div class="pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700">{{ $review->user->name }}</span>
                                        <div class="flex text-amber-400 gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $review->rating)
                                                    <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                                                @else
                                                    <i data-lucide="star" class="w-3 h-3 text-gray-300"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ Str::limit($review->comment, 80) }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if($artisan->reviews->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="#" class="text-xs text-blue-600 hover:text-blue-700">Voir tous les avis</a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="message-circle" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400">Aucun avis</p>
                        </div>
                    @endif
                </div>

                <!-- Demandes de contact -->
                <div class="bg-sidebar rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">Demandes de contact</h2>
                    @if ($artisan->contacts->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($artisan->contacts->take(5) as $contact)
                                <div class="pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                    <p class="text-sm font-medium text-gray-700">{{ $contact->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $contact->phone }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $contact->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if($artisan->contacts->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="#" class="text-xs text-blue-600 hover:text-blue-700">Voir toutes les demandes</a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="phone" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400">Aucune demande</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection