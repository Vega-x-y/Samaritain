@extends('layouts.base')

@section('content')
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Vous avez été invité à rejoindre l'équipe de l'agence. Veuillez créer votre compte.
    </div>
    <form method="POST" action="{{ route('admin.invitations.accept') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="dark:text-gray-300">Nom</label>
            <input type="text" name="name" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 w-full">
        </div>
        <div class="mt-4">
            <label class="dark:text-gray-300">Mot de passe</label>
            <input type="password" name="password" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 w-full">
        </div>
        <div class="mt-4">
            <label class="dark:text-gray-300">Confirmer mot de passe</label>
            <input type="password" name="password_confirmation" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 w-full">
        </div>
        <button type="submit" class="mt-4 bg-blue-600 dark:bg-primary-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:hover:bg-primary-700 transition">Créer mon compte</button>
    </form>
@endsection