@extends('layouts.base')

@section('title', 'Nous contacter')

@section('content')
    <div class="font-body bg-background dark:bg-gray-950 text-[#0F0E0C] dark:text-white antialiased min-h-screen">
        <div class="max-w-4xl mx-auto px-6 py-10 pb-20">

            {{-- Breadcrumb --}}
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-2 text-xs text-[#6B6660] dark:text-gray-400 mb-10 font-body">
                <a href="{{ route('index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Accueil</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="dark:text-gray-300">Contact</span>
            </nav>

            {{-- Header --}}
            <header class="mb-10">
                <h1 class="font-display font-semibold leading-[1.1] tracking-tight text-[#0F0E0C] dark:text-white"
                    style="font-size: clamp(1.8rem, 3.5vw, 2.5rem);">
                    Contactez-nous
                </h1>
                <p class="text-[#6B6660] dark:text-gray-400 mt-2 text-sm">
                    Vous avez une question ou besoin d'informations ? Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.
                </p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-10 items-start">

                {{-- ── FORM ── --}}
                <div>
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
                                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <x-form.input
                                name="name"
                                label="Nom"
                                placeholder="Votre nom"
                                :value="old('name')"
                                required
                            />

                            <x-form.input
                                name="email"
                                type="email"
                                label="Email"
                                placeholder="votre@email.com"
                                :value="old('email')"
                                required
                            />
                        </div>

                        <x-form.input
                            name="phone"
                            type="tel"
                            label="Téléphone (optionnel)"
                            placeholder="+243 XX XXX XXXX"
                            :value="old('phone')"
                        />

                        <x-form.input
                            name="subject"
                            label="Sujet"
                            placeholder="Sujet de votre message"
                            :value="old('subject')"
                            required
                        />

                        <x-form.textarea
                            name="message"
                            label="Message"
                            placeholder="Écrivez votre message ici..."
                            :value="old('message')"
                            rows="6"
                            required
                        />

                        <div class="pt-2">
                            <button type="submit"
                                class="inline-flex w-full sm:w-auto items-center justify-center gap-x-1.5 shrink-0 transition-colors duration-100 text-sm/5 font-medium shadow-none rounded-[var(--radius)] bg-[var(--primary)] text-[var(--primary-foreground)] hover:bg-[color-mix(in_oklab,var(--primary)_90%,transparent)] focus:bg-[color-mix(in_oklab,var(--primary)_90%,transparent)] active:bg-[var(--primary)] h-9 text-center px-6 py-2 cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Envoyer le message
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── RIGHT COLUMN : Contact Info ── --}}
                <div class="lg:sticky lg:top-6">
                    <div class="border border-accent rounded-2xl p-5 space-y-4">
                        <div>
                            <p class="text-[0.68rem] font-medium text-[#6B6660] dark:text-gray-400 uppercase tracking-wider mb-1">
                                Informations de contact
                            </p>
                            <h3 class="font-display font-semibold text-lg text-[#0F0E0C] dark:text-white">
                                Nos coordonnées
                            </h3>
                        </div>

                        <hr class="border-accent">

                        <div class="space-y-4">
                            {{-- Email --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="mail" class="w-4 h-4 text-primary"></i>
                                </div>
                                <div>
                                    <span class="text-[0.68rem] font-medium text-[#6B6660] dark:text-gray-400 uppercase tracking-wider">Email</span>
                                    <p class="text-sm text-[#0F0E0C] dark:text-white font-medium break-all">{{ $email }}</p>
                                </div>
                            </div>

                            {{-- Phone / WhatsApp --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="phone" class="w-4 h-4 text-primary"></i>
                                </div>
                                <div>
                                    <span class="text-[0.68rem] font-medium text-[#6B6660] dark:text-gray-400 uppercase tracking-wider">Téléphone</span>
                                    <p class="text-sm text-[#0F0E0C] dark:text-white font-medium">{{ $whatsapp }}</p>
                                </div>
                            </div>
                        </div>

                        <hr class="border-accent">

                        {{-- Availability Schedule --}}
                        <div>
                            <span class="text-[0.68rem] font-medium text-[#6B6660] dark:text-gray-400 uppercase tracking-wider mb-3 block">Horaires de disponibilité</span>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-[#6B6660] dark:text-gray-400">Lundi - Vendredi</span>
                                    <span class="text-[#0F0E0C] dark:text-white font-medium">8h00 - 18h00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#6B6660] dark:text-gray-400">Samedi</span>
                                    <span class="text-[#0F0E0C] dark:text-white font-medium">9h00 - 14h00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#6B6660] dark:text-gray-400">Dimanche</span>
                                    <span class="text-[#0F0E0C] dark:text-white font-medium">Fermé</span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-accent">

                        {{-- Response Time --}}
                        <div class="bg-primary/5 dark:bg-primary/10 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-primary flex-shrink-0 mt-0.5"></i>
                                <div>
                                    <p class="text-xs font-medium text-[#0F0E0C] dark:text-white mb-1">Temps de réponse</p>
                                    <p class="text-xs text-[#6B6660] dark:text-gray-400">
                                        Nous nous engageons à vous répondre dans un délai maximum de 24 heures ouvrables.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
