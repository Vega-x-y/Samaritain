@extends('layouts.dashboard')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Détails du membre : {{ $member->name }}</h2>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Nom :</strong> {{ $member->name }}</div>
                    <div><strong>Email :</strong> {{ $member->email }}</div>
                    <div><strong>Rôle :</strong> {{ $member->roles->first()->name ?? 'Aucun' }}</div>
                    <div><strong>Statut :</strong> {{ $member->is_active ? 'Actif' : 'Inactif' }}</div>
                    <div><strong>Membre depuis :</strong> {{ $member->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.members.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
                </div>
            </div>
        </div>
    </div>
@endsection
