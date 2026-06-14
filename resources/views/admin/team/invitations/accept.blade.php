@extends('layouts.base')

@section('content')
    <div class="mb-4 text-sm text-gray-600">
        Vous avez été invité à rejoindre l'équipe de l'agence. Veuillez créer votre compte.
    </div>
    <form method="POST" action="{{ route('admin.invitations.accept') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label>Nom</label>
            <input type="text" name="name" required class="rounded-md border-gray-300 w-full">
        </div>
        <div class="mt-4">
            <label>Mot de passe</label>
            <input type="password" name="password" required class="rounded-md border-gray-300 w-full">
        </div>
        <div class="mt-4">
            <label>Confirmer mot de passe</label>
            <input type="password" name="password_confirmation" required class="rounded-md border-gray-300 w-full">
        </div>
        <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Créer mon compte</button>
    </form>
@endsection
