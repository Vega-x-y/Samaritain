@extends('layouts.base')

@section('title', 'Scan du Pass')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
            <div class="p-6 sm:p-8">
                @if ($result['valid'])
                    <div class="text-center">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20">
                            <i data-lucide="check-circle-2" class="w-8 h-8 text-green-500"></i>
                        </div>
                        <h2 class="text-lg font-semibold mb-6 text-green-600 dark:text-green-400">Pass valide</h2>

                        <div class="text-left rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Titulaire</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $result['pass']->holder_name }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Expiration</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $result['pass']->expiration_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Visites restantes</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $result['pass']->remaining_visits }}/{{ $result['pass']->allowed_visits }}
                                </span>
                            </div>
                        </div>

                        <form action="{{ route('scan.process') }}" method="POST" class="mt-6">
                            @csrf
                            <input type="hidden" name="uuid" value="{{ $result['pass']->uuid }}">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold text-sm py-2.5 px-6 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                Valider la visite
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                            <i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>
                        </div>
                        <h2 class="text-lg font-semibold mb-2 text-red-600 dark:text-red-400">{{ $result['message'] }}</h2>

                        @if ($result['pass'])
                            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                                Titulaire : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $result['pass']->holder_name }}</span>
                            </p>
                        @else
                            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">&nbsp;</p>
                        @endif

                        <a href="{{ route('scan.index') }}"
                            class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 active:bg-gray-800 text-white font-semibold text-sm py-2.5 px-6 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <i data-lucide="scan-line" class="w-4 h-4"></i>
                            Nouveau scan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection