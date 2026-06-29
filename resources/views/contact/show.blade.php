@extends('layouts.base')

@section('title', 'Contacter l\'agence - ' . $annonce->title)

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        {{-- En-tête --}}
        <div class="bg-card rounded-xl border border-border p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground mb-2">
                        Contacter l'agence
                    </h1>
                    <p class="text-muted">
                        Envoyez un message à l'agence pour l'annonce :
                        <a href="{{ route('property.show', $annonce) }}" class="text-primary hover:underline font-medium">
                            {{ $annonce->title }}
                        </a>
                    </p>
                    <p class="text-sm text-muted mt-1">
                        Référence : <span class="font-mono">{{ $annonce->reference }}</span>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('property.show', $annonce) }}"
                        class="inline-flex items-center gap-2 text-sm text-muted hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour à l'annonce
                    </a>
                </div>
            </div>
        </div>

        {{-- Messages flash --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-success/10 border border-success/20 p-4 text-success">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-destructive/10 border border-destructive/20 p-4 text-destructive">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Formulaire --}}
        <div class="bg-card rounded-xl border border-border p-6">
            <form action="{{ route('contact.agency.send', $annonce) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Honeypot (champ caché) --}}
                <input type="text" name="website" class="hidden" aria-hidden="true" tabindex="-1" autocomplete="off">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nom --}}
                    <div>
                        <x-form.input name="name" label="Nom complet" placeholder="Votre nom" icon="user"
                            required />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-form.input name="email" label="Adresse email" type="email" placeholder="vous@exemple.com"
                            icon="mail" required />
                    </div>
                </div>

                {{-- Téléphone --}}
                <div>
                    <x-form.input name="phone" label="Téléphone (facultatif)" type="tel"
                        placeholder="+33 6 12 34 56 78" icon="phone" />
                </div>

                {{-- Message --}}
                <div>
                    <x-form.textarea name="message" label="Message" placeholder="Détaillez votre demande..." rows="6"
                        required></x-form.textarea>
                </div>

                {{-- Boutons --}}
                <div class="flex items-center gap-4 pt-4 border-t border-border">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-background shadow-sm hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-hidden transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Envoyer le message
                    </button>

                    <a href="{{ route('property.show', $annonce) }}"
                        class="inline-flex items-center justify-center rounded-lg px-6 py-2.5 text-sm font-medium text-muted hover:text-foreground hover:bg-secondary/50 transition-colors">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
