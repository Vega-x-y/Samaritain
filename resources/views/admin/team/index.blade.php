@extends('layouts.dashboard')

@section('title', 'Membres de l\'équipe')

@section('content')
    @if (!$members->isEmpty())
        <div class="flex justify-between">
            <h1>Liste des membres</h1>
            <x-btn href="{{ route('admin.invitations.create') }}">
                <x-slot:prefix>
                    <i data-lucide="user-plus"></i>
                </x-slot:prefix>
                Inviter un membre
            </x-btn>
        </div>
        <x-container-dashed>
            <div x-data="memberActions()" @keydown.escape="closeActionModal()">
                <!-- Barre de recherche -->
                <div class="mb-4">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" id="search" placeholder="Rechercher un membre..." 
                               class="w-full md:w-96 pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="overflow-x-auto bg-sidebar rounded-lg shadow-sm">
                    <table class="w-full text-xs text-gray-600">
                        <thead class="border-b border-b-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Photo</th>
                                <th class="px-4 py-3 text-left">Nom</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Rôle</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-left">Date d'ajout</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($members as $member)
                                <tr>
                                    <td class="px-4 py-3">
                                        <img src="{{ $member->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=3b82f6&color=fff' }}"
                                             class="w-8 h-8 rounded-full object-cover">
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $member->name }}</td>
                                    <td class="px-4 py-3">{{ $member->email }}</td>
                                    <td class="px-4 py-3">
                                        @if($member->roles->first())
                                            <span class="px-2 py-1 text-xs font-medium text-blue-500 bg-blue-100 rounded-full">
                                                {{ $member->roles->first()->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Aucun</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($member->is_active)
                                            <span class="px-2 py-1 text-xs font-medium text-green-500 bg-green-100 rounded-full">Actif</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-red-500 bg-red-100 rounded-full">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $member->created_at->format('d/m/Y') }}</td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.members.show', $member) }}" class="block text-blue-500" title="Voir">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('admin.members.edit', $member) }}" class="block text-blue-500" title="Modifier">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            @if ($member->is_active)
                                                <button @click="openActionModal('deactivate', '{{ route('admin.members.deactivate', $member) }}', '{{ $member->name }}')" 
                                                        class="block text-orange-500" title="Désactiver">
                                                    <i data-lucide="user-x" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button @click="openActionModal('activate', '{{ route('admin.members.activate', $member) }}', '{{ $member->name }}')" 
                                                        class="block text-green-500" title="Réactiver">
                                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                            <button @click="openActionModal('delete', '{{ route('admin.members.destroy', $member) }}', '{{ $member->name }}')" 
                                                    class="block text-destructive" title="Supprimer">
                                                <i data-lucide="trash" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 mb-2 text-xs">
                    {{ $members->links() }}
                </div>

                <!-- Modal de confirmation -->
                <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeActionModal()">
                    <div class="relative w-full max-w-md rounded-lg bg-background p-6 shadow-lg" @click.stop>
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                 :class="{
                                    'bg-red-100': actionType === 'delete',
                                    'bg-orange-100': actionType === 'deactivate',
                                    'bg-green-100': actionType === 'activate'
                                 }">
                                <i :class="{
                                        'lucide-alert-octagon text-red-600': actionType === 'delete',
                                        'lucide-user-x text-orange-600': actionType === 'deactivate',
                                        'lucide-user-check text-green-600': actionType === 'activate'
                                     }" 
                                   class="h-6 w-6"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900" x-text="modalTitle"></h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span x-html="modalMessage"></span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <x-btn @click="closeActionModal()" style="outline">
                                Annuler
                            </x-btn>
                            <form :action="actionUrl" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="_method" :value="methodType">
                                <x-btn type="submit">
                                    <span x-text="confirmButtonText"></span>
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
            <x-btn href="{{ route('admin.invitations.create') }}">
                <x-slot:prefix>
                    <i data-lucide="user-plus"></i>
                </x-slot:prefix>
                Inviter le premier membre
            </x-btn>
        </div>
        <x-empty title="Aucun membre trouvé" description="Invitez votre premier membre pour commencer à constituer votre équipe">
            <x-slot:icon>
                <i data-lucide="users"></i>
            </x-slot:icon>
        </x-empty>
    @endif

    <script>
        function memberActions() {
            return {
                isModalOpen: false,
                actionType: '',
                actionUrl: '',
                memberName: '',
                methodType: 'PATCH',
                
                get modalTitle() {
                    if (this.actionType === 'delete') return 'Supprimer le membre';
                    if (this.actionType === 'deactivate') return 'Désactiver le membre';
                    return 'Réactiver le membre';
                },
                
                get modalMessage() {
                    if (this.actionType === 'delete') {
                        return `Êtes-vous sûr de vouloir supprimer définitivement <strong>${this.memberName}</strong> ? Cette action est irréversible.`;
                    }
                    if (this.actionType === 'deactivate') {
                        return `Êtes-vous sûr de vouloir désactiver <strong>${this.memberName}</strong> ? L'utilisateur ne pourra plus se connecter.`;
                    }
                    return `Êtes-vous sûr de vouloir réactiver <strong>${this.memberName}</strong> ? L'utilisateur pourra à nouveau se connecter.`;
                },
                
                get confirmButtonText() {
                    if (this.actionType === 'delete') return 'Supprimer';
                    if (this.actionType === 'deactivate') return 'Désactiver';
                    return 'Réactiver';
                },
                
                openActionModal(type, url, name) {
                    this.actionType = type;
                    this.actionUrl = url;
                    this.memberName = name;
                    
                    if (type === 'delete') {
                        this.methodType = 'DELETE';
                    } else {
                        this.methodType = 'PATCH';
                    }
                    
                    this.isModalOpen = true;
                },
                
                closeActionModal() {
                    this.isModalOpen = false;
                    this.actionType = '';
                    this.actionUrl = '';
                    this.memberName = '';
                    this.methodType = 'PATCH';
                }
            }
        }
    </script>
@endsection