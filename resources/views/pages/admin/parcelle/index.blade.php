@extends('layouts.dashboard')

@section('content')
    @if (!$parcelles->isEmpty())
        <div class="flex justify-between">
            <h1>Liste des parcelles</h1>
            <x-btn href="{{ route('admin.parcelle.create') }}">
                <x-slot:prefix>
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Ajouter une parcelle
            </x-btn>
        </div>
    @else
        <div class="flex justify-between">
            <div></div>
            <x-btn href="{{ route('admin.property.create') }}">
                <x-slot:prefix>
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Ajouter la première parcelle
            </x-btn>
        </div>
        <x-empty title="Aucune parcelle trouvée" description="Créer une première parcelle pour commencer">
            <x-slot:icon>
                <i data-lucide="land-plot"></i>
            </x-slot:icon>
        </x-empty>
    @endif

    <script>
        function deleteModal() {
            return {
                isOpen: false,
                deleteAction: '',
                propertyTitle: '',
                openModal(action, title) {
                    this.deleteAction = action;
                    this.propertyTitle = title;
                    this.isOpen = true;
                },
                closeModal() {
                    this.isOpen = false;
                    this.deleteAction = '';
                    this.propertyTitle = '';
                }
            }
        }
    </script>
@endsection
