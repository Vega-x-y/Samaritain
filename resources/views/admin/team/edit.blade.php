@extends('layouts.dashboard')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier {{ $member->name }}</h2>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.members.update', $member) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $member->name) }}"
                            class="w-full rounded-lg border-gray-300">
                        @error('name')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block">Email</label>
                        <input type="email" name="email" value="{{ old('email', $member->email) }}"
                            class="w-full rounded-lg border-gray-300">
                        @error('email')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block">Rôle</label>
                        <select name="role_id" class="w-full rounded-lg border-gray-300">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $member->roles->first()->id == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label><input type="checkbox" name="is_active" value="1"
                                {{ $member->is_active ? 'checked' : '' }}> Actif</label>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
@endsection
