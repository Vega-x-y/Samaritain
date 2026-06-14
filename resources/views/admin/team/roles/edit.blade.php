@extends('layouts.dashboard')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Modifier le rôle : {{ ucfirst($role->name) }}</h2>
                    </div>

                    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nom du rôle -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom du rôle</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                        <span
                                            class="text-sm text-gray-700">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.roles.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Annuler
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
