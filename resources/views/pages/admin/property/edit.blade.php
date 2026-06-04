@extends('layouts.dashboard')

@section('content')
    <h1>Modifier le bien</h1>
    <x-container-dashed>
        <form action="{{ route('admin.property.update', $property) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="title" label="Titre du bien" :value="$property->title"/>
                <x-form.input name="surface" label="Surface (m²)" type="number" step="0.01" :value="$property->surface" />
                <x-form.input name="price" label="Prix" type="number" step="0.01" :value="$property->price" />
            </div>

            <x-form.textarea name="description" label="Description" :value="$property->description" />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="rooms" label="Pièces" type="number" :value="$property->rooms" />
                <x-form.input name="bedrooms" label="Chambres" type="number" :value="$property->bedrooms" />
                <x-form.input name="floor" label="Étages" type="number" :value="$property->floor" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="address" label="Adresse" :value="$property->address" />
            </div>

            <div class="flex items-center gap-3">
                <x-btn type="submit">
                    Modifier le bien
                </x-btn>
                <x-btn href="{{ route('admin.property.index') }}" style="outline">
                    Annuler
                </x-btn>
            </div>
        </form>
    </x-container-dashed>
@endsection