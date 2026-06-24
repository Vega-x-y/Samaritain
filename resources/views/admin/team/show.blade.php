@extends('layouts.dashboard')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">Détails du membre : {{ $member->name }}</h2>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-gray-900/50 sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="dark:text-gray-300"><strong class="dark:text-white">Nom :</strong> {{ $member->name }}</div>
                    <div class="dark:text-gray-300"><strong class="dark:text-white">Email :</strong> {{ $member->email }}</div>
                    <div class="dark:text-gray-300"><strong class="dark:text-white">Rôle :</strong> {{ $member->roles->first()->name ?? 'Aucun' }}</div>
                    <div class="dark:text-gray-300"><strong class="dark:text-white">Statut :</strong> 
                        @if ($member->is_active)
                            <span class="text-green-500 dark:text-green-400">Actif</span>
                        @else
                            <span class="text-red-500 dark:text-red-400">Inactif</span>
                        @endif
                    </div>
                    <div class="dark:text-gray-300"><strong class="dark:text-white">Membre depuis :</strong> {{ $member->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.members.index') }}" class="bg-gray-500 dark:bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-600 dark:hover:bg-gray-600 transition">Retour</a>
                </div>
            </div>
        </div>
    </div>
@endsection