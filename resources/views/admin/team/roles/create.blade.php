@extends('layouts.dashboard')

@section('content')
    <h2>Créer un rôle</h2>
    <div class="py-12">
        <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="mb-4">
                    <label>Nom du rôle</label>
                    <input type="text" name="name" required class="w-full rounded border-gray-300">
                </div>
                <div class="mb-4">
                    <label>Permissions</label><br>
                    @foreach ($permissions as $perm)
                        <label><input type="checkbox" name="permissions[]" value="{{ $perm->id }}">
                            {{ $perm->name }}</label><br>
                    @endforeach
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
            </form>
        </div>
    </div>
@endsection
