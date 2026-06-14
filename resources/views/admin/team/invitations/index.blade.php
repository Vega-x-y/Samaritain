@extends('layouts.dashboard')

@section('title', 'Invitations en attente')

@section('content')
    @if (!$invitations->isEmpty())
        <div class="flex justify-between">
            <h1>Liste des invitations</h1>
            <x-btn href="{{ route('admin.invitations.create') }}">
                <x-slot:prefix>
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Nouvelle invitation
            </x-btn>
        </div>
        <x-container-dashed>
            <div x-data="invitationActions()" @keydown.escape="closeCancelModal()">
                <div class="overflow-x-auto bg-sidebar rounded-lg shadow-sm">
                    <table class="w-full text-xs text-gray-600">
                        <thead class="border-b border-b-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Rôle</th>
                                <th class="px-4 py-3 text-left">Expire le</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($invitations as $inv)
                                <tr>
                                    <td class="px-4 py-3">{{ $inv->email }}</td>
                                    <td class="px-4 py-3">{{ $inv->role->name }}</td>
                                    <td class="px-4 py-3">{{ $inv->expires_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($inv->accepted_at)
                                            <span class="px-2 py-1 text-xs font-medium text-green-500 bg-green-300 rounded-full">Acceptée</span>
                                        @elseif ($inv->isExpired())
                                            <span class="px-2 py-1 text-xs font-medium text-red-500 bg-red-300 rounded-full">Expirée</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-500 bg-yellow-300 rounded-full">En attente</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if (!$inv->accepted_at && !$inv->isExpired())
                                            <div class="flex items-center justify-center gap-2">
                                                <form action="{{ route('admin.invitations.resend', $inv) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="block text-blue-500">
                                                        <i data-lucide="send" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                                <button @click="openCancelModal('{{ route('admin.invitations.destroy', $inv) }}', '{{ $inv->email }}')" class="block text-destructive">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 mb-2 text-xs">
                    {{ $invitations->links() }}
                </div>

                <!-- Modal de confirmation d'annulation -->
                <div x-show="isCancelModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeCancelModal()">
                    <div class="relative w-full max-w-md rounded-lg bg-background p-6 shadow-lg" @click.stop>
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                                <i data-lucide="alert-octagon" class="h-6 w-6 text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Annuler l'invitation</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Êtes-vous sûr de vouloir annuler l'invitation pour <strong x-text="invitationEmail"></strong> ? Cette action est irréversible.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <x-btn @click="closeCancelModal()" style="outline">
                                Annuler
                            </x-btn>
                            <form :action="cancelAction" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-btn type="submit" style="destructive">
                                    Confirmer l'annulation
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
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Créer la première invitation
            </x-btn>
        </div>
        <x-empty title="Aucune invitation trouvée" description="Créez une première invitation pour commencer">
            <x-slot:icon>
                <i data-lucide="mail"></i>
            </x-slot:icon>
        </x-empty>
    @endif

    <script>
        function invitationActions() {
            return {
                isCancelModalOpen: false,
                cancelAction: '',
                invitationEmail: '',
                openCancelModal(action, email) {
                    this.cancelAction = action;
                    this.invitationEmail = email;
                    this.isCancelModalOpen = true;
                },
                closeCancelModal() {
                    this.isCancelModalOpen = false;
                    this.cancelAction = '';
                    this.invitationEmail = '';
                }
            }
        }
    </script>
@endsection