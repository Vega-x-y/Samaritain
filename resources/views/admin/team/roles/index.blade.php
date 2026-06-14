@extends('layouts.dashboard')

@section('title', 'Rôles et permissions')

@section('content')
    @if (!$roles->isEmpty())
        <div class="flex justify-between">
            <h1>Liste des rôles</h1>
            <x-btn href="{{ route('admin.roles.create') }}">
                <x-slot:prefix>
                    <i data-lucide="shield-plus"></i>
                </x-slot:prefix>
                Créer un rôle
            </x-btn>
        </div>
        <x-container-dashed>
            <div x-data="roleActions()" @keydown.escape="closeDeleteModal()">
                <div class="overflow-x-auto bg-sidebar rounded-lg shadow-sm">
                    <table class="w-full text-xs text-gray-600">
                        <thead class="border-b border-b-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Rôle</th>
                                <th class="px-4 py-3 text-left">Permissions</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="px-4 py-3 font-medium">
                                        {{ ucfirst($role->name) }}
                                        @if($role->name === 'admin')
                                            <span class="ml-2 px-2 py-0.5 text-xs font-medium text-blue-500 bg-blue-100 rounded-full">Principal</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($role->permissions as $permission)
                                                <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded">
                                                    {{ $permission->name }}
                                                </span>
                                            @endforeach
                                            @if($role->permissions->isEmpty())
                                                <span class="text-gray-400">Aucune permission</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="block text-blue-500">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            @if($role->name !== 'admin')
                                                <button @click="openDeleteModal('{{ route('admin.roles.destroy', $role) }}', '{{ ucfirst($role->name) }}')" class="block text-destructive">
                                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button class="block text-gray-300 cursor-not-allowed" disabled title="Le rôle admin ne peut pas être supprimé">
                                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Modal de confirmation de suppression -->
                <div x-show="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeDeleteModal()">
                    <div class="relative w-full max-w-md rounded-lg bg-background p-6 shadow-lg" @click.stop>
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                                <i data-lucide="alert-octagon" class="h-6 w-6 text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Supprimer le rôle</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Êtes-vous sûr de vouloir supprimer le rôle <strong x-text="roleName"></strong> ? Cette action est irréversible.
                                </p>
                                @if(isset($role) && $role->users && $role->users->count() > 0)
                                    <p class="mt-2 text-sm text-red-600">
                                        ⚠️ Attention : Ce rôle est actuellement attribué à {{ $role->users->count() }} utilisateur(s).
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <x-btn @click="closeDeleteModal()" style="outline">
                                Annuler
                            </x-btn>
                            <form :action="deleteAction" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-btn type="submit" style="destructive">
                                    Supprimer
                                </x-btn>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </x-container-dashed>
    @else
        <div class="flex justify-between">
            <div></div>
            <x-btn href="{{ route('admin.roles.create') }}">
                <x-slot:prefix>
                    <i data-lucide="shield-plus"></i>
                </x-slot:prefix>
                Créer le premier rôle
            </x-btn>
        </div>
        <x-empty title="Aucun rôle trouvé" description="Créez un premier rôle pour commencer à gérer les permissions">
            <x-slot:icon>
                <i data-lucide="shield"></i>
            </x-slot:icon>
        </x-empty>
    @endif

    <script>
        function roleActions() {
            return {
                isDeleteModalOpen: false,
                deleteAction: '',
                roleName: '',
                openDeleteModal(action, name) {
                    this.deleteAction = action;
                    this.roleName = name;
                    this.isDeleteModalOpen = true;
                },
                closeDeleteModal() {
                    this.isDeleteModalOpen = false;
                    this.deleteAction = '';
                    this.roleName = '';
                }
            }
        }
    </script>
@endsection