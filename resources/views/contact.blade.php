@extends('layouts.base')

@section('title', 'Nous contacter')

@section('content')
    <x-blade-components::layout.container>
        {{-- Header Section --}}
        <section class="bg-gradient-to-br from-primary to-primary/80 rounded-2xl overflow-hidden relative mt-2 mb-8">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle at 70% 50%, #ffffff 0%, transparent 60%);">
            </div>

            <div class="relative px-8 py-16 md:px-14 md:py-20 text-center">
                <div class="inline-flex items-center gap-2 bg-white/15 text-white text-xs font-medium px-3 py-1.5 rounded-full mb-6">
                    <i data-lucide="headset" class="w-3.5 h-3.5"></i>
                    Support disponible 24h/24
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    Nous sommes là pour vous aider
                </h1>

                <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto">
                    Contactez-nous par email ou WhatsApp. Notre équipe vous répond en moins de 24h.
                </p>
            </div>
        </section>

        {{-- Contact Options --}}
        <section class="max-w-5xl mx-auto px-6 pb-16">
            <div class="grid md:grid-cols-2 gap-6">

                {{-- Contact Form Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 md:col-span-1">
                    <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mb-5">
                        <i data-lucide="mail" class="w-7 h-7 text-blue-600 dark:text-blue-400"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        Envoyez-nous un message
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 leading-relaxed">
                        Remplissez le formulaire ci-dessous. Nous vous répondrons sous 24h avec toutes les informations nécessaires.
                    </p>

                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
                                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <x-form.input
                            name="name"
                            label="Nom complet"
                            icon="user"
                            placeholder="Votre nom"
                            required
                        />

                        <x-form.input
                            name="email"
                            type="email"
                            label="Adresse email"
                            icon="mail"
                            placeholder="votre.email@exemple.com"
                            required
                        />

                        <x-form.input
                            name="phone"
                            type="tel"
                            label="Numéro de téléphone (optionnel)"
                            icon="phone"
                            placeholder="+243 XX XXX XXXX"
                        />

                        <x-form.input
                            name="subject"
                            label="Sujet"
                            icon="tag"
                            placeholder="L'objet de votre message"
                            required
                        />

                        <x-form.textarea
                            name="message"
                            label="Votre message"
                            rows="5"
                            placeholder="Décrivez votre demande en détail..."
                            required
                        />

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3.5 rounded-xl transition"
                        >
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Envoyer le message
                        </button>
                    </form>

                    <div class="flex items-start gap-2 mt-6 text-xs text-gray-500 dark:text-gray-400">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0"></i>
                        <p>Vos informations sont confidentielles et ne seront pas partagées avec des tiers.</p>
                    </div>
                </div>

                {{-- WhatsApp Contact Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition">
                        <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        Contactez-nous sur WhatsApp
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 leading-relaxed">
                        Discutez directement avec notre équipe sur WhatsApp. Réponse rapide pour toutes vos questions urgentes.
                    </p>

                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 mb-6">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Notre numéro WhatsApp</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $whatsapp }}
                        </p>
                    </div>

                    <a href="https://wa.me/{{ str_replace([' ', '+'], '', $whatsapp) }}?text=Bonjour,%20je%20souhaiterais%20obtenir%20des%20informations%20concernant..."
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        Ouvrir WhatsApp
                    </a>

                    <div class="flex items-start gap-2 mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0"></i>
                        <p>Parfait pour les questions rapides et un échange instantané</p>
                    </div>
                </div>

            </div>
        </section>

        {{-- Additional Info Section --}}
        <section class="max-w-5xl mx-auto px-6 pb-16">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-8 md:p-10">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        Horaires de disponibilité
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Notre équipe est disponible pour répondre à toutes vos questions
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="clock" class="w-5 h-5 text-primary"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Lundi - Vendredi</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">8h00 - 18h00</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Samedi</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">9h00 - 14h00</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="moon" class="w-5 h-5 text-primary"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Dimanche</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Fermé</p>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <div class="flex items-start gap-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-semibold text-blue-900 dark:text-blue-300 mb-1">Engagement de réponse</h5>
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                Nous nous engageons à répondre à toutes vos demandes dans un délai maximum de 24 heures ouvrables.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </x-blade-components::layout.container>
@endsection
