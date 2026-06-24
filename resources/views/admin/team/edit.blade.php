@extends('layouts.dashboard')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">Modifier {{ $member->name }}</h2>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-gray-900/50 sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.members.update', $member) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block dark:text-gray-300">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $member->name) }}"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        @error('name')
                            <span class="text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block dark:text-gray-300">Email</label>
                        <input type="email" name="email" value="{{ old('email', $member->email) }}"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        @error('email')
                            <span class="text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block dark:text-gray-300">Rôle</label>
                        <select name="role_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $member->roles->first()->id == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <span class="text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="dark:text-gray-300">
                            <input type="checkbox" name="is_active" value="1"
                                {{ $member->is_active ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 dark:text-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                            Actif
                        </label>
                    </div>
                    <button type="submit" class="bg-blue-600 dark:bg-primary-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:hover:bg-primary-700 transition">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
@endsection