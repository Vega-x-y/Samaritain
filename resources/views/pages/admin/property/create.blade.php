@extends('layouts.dashboard')

@section('content')
    <h1>Créer un bien</h1>
    <x-container-dashed>
        <form action="{{ route('admin.property.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="title" label="Titre du bien"/>
                <x-form.input name="surface" label="Surface (m²)" type="number" step="0.01" />
                <x-form.input name="price" label="Prix" type="number" step="0.01"/>
            </div>

            <x-form.textarea name="description" label="Description" />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="rooms" label="Pièces" type="number" />
                <x-form.input name="bedrooms" label="Chambres" type="number" />
                <x-form.input name="floor" label="Étages" type="number" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="address" label="Adresse"/>
            </div>

            <div class="flex items-center gap-3">
                <x-btn type="submit">
                    Ajouter le bien
                </x-btn>
                <x-btn href="{{ route('admin.property.index') }}" style="outline">
                    Annuler
                </x-btn>
            </div>
        </form>
    </x-container-dashed>
@endsection
