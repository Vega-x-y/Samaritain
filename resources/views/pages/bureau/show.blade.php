@extends('layouts.base')

@section('title', $bureau->name)

@section('content')
    <div class="font-body bg-background dark:bg-gray-950 text-[#0F0E0C] dark:text-white antialiased min-h-screen">
        <div class="max-w-7xl mx-auto px-6 py-10 pb-20">

            {{-- Breadcrumb --}}
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-2 text-xs text-[#6B6660] dark:text-gray-400 mb-10 font-body">
                <a href="{{ route('index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Accueil</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <a href="{{ route('bureau.index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Bureaux</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="dark:text-gray-300">{{ $bureau->name }}</span>
            </nav>

            {{-- Header --}}
            <header class="mb-8">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="font-display font-semibold leading-[1.1] tracking-tight text-[#0F0E0C] dark:text-white"
                            style="font-size: clamp(2rem, 4.5vw, 3.5rem); max-width: 28ch;">
                            {{ $bureau->name }}
                        </h1>

                        {{-- Location --}}
                        <div class="flex items-center gap-2 mt-3">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#6B6660] dark:text-gray-400"></i>
                            <span class="text-[0.83rem] text-[#6B6660] dark:text-gray-400">
                                {{ $bureau->location ?? 'Localisation non spécifiée' }}
                            </span>
                        </div>
                    </div>

                    @if($bureau->active)
                        <span class="px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-full">
                            <i data-lucide="check-circle" class="inline w-3 h-3"></i> Actif
                        </span>
                    @endif
                </div>
            </header>

            {{-- Main grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                {{-- LEFT COLUMN --}}
                <div class="lg:col-span-2">
                    {{-- Description --}}
                    @if ($bureau->description)
                        <section class="mb-12">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">À propos</h2>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $bureau->description }}</p>
                        </section>
                    @endif
                </div>

                {{-- RIGHT SIDEBAR --}}
                <aside class="lg:col-span-1">
                    <div class="bg-card dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informations de contact</h3>

                        @if($bureau->phone)
                            <div class="space-y-2">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest">Téléphone</p>
                                <a href="tel:{{ $bureau->phone }}" class="text-lg font-semibold text-primary dark:text-primary-400 hover:underline">
                                    {{ $bureau->phone }}
                                </a>
                            </div>
                        @endif

                        @if($bureau->email)
                            <div class="space-y-2">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest">Email</p>
                                <a href="mailto:{{ $bureau->email }}" class="text-sm text-primary dark:text-primary-400 hover:underline break-all">
                                    {{ $bureau->email }}
                                </a>
                            </div>
                        @endif

                        @if($bureau->location)
                            <div class="space-y-2">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest">Localisation</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ $bureau->location }}</p>
                            </div>
                        @endif

                        <hr class="my-4 border-gray-200 dark:border-gray-700">

                        <div class="space-y-3">
                            <a href="{{ route('bureau.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition font-medium">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                Retour aux bureaux
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
